<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use App\Services\TuyaCameraService;
use App\Support\Satker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CctvController extends Controller
{
    public function __construct(private TuyaCameraService $tuya)
    {
    }

    /**
     * Daftar kamera yang boleh dilihat (satker → PPK → paket) + pemutar kamera terpilih.
     */
    public function index(Request $request)
    {
        $cctvs = Cctv::visibleTo($request->user())
            ->where('aktif', true)
            ->with('packet.ppk')
            ->orderBy('nama')
            ->get();

        $kelompok = [];
        foreach ($cctvs as $c) {
            $slug = $c->packet?->satker_group ?? 'lainnya';
            $ppk = $c->packet?->ppk?->jabatan ?? 'Belum ada PPK';
            $kelompok[$slug][$ppk][] = $c;
        }

        // Urutkan satker sesuai config, PPK menurut abjad
        $urut = array_flip(array_keys(Satker::groups()));
        uksort($kelompok, fn ($a, $b) => ($urut[$a] ?? 99) <=> ($urut[$b] ?? 99));
        foreach ($kelompok as &$perPpk) {
            ksort($perPpk);
        }
        unset($perPpk);

        $terpilih = $cctvs->firstWhere('id', (int) $request->query('kamera')) ?? $cctvs->first();

        return view('v_cctv', [
            'configured' => $this->tuya->isConfigured(),
            'kelompok' => $kelompok,
            'namaSatker' => Satker::getNamesMap(),
            'terpilih' => $terpilih,
            'jumlah' => $cctvs->count(),
        ]);
    }

    /**
     * URL siaran baru untuk pemutar (dipanggil saat tombol Putar ditekan / URL kedaluwarsa).
     */
    public function stream(Request $request, Cctv $cctv): JsonResponse
    {
        $this->authorizeView($request, $cctv);
        $type = $request->query('type') === 'hls' ? 'hls' : 'flv';

        if (! $this->tuya->isConfigured()) {
            return response()->json(['message' => 'CCTV belum dikonfigurasi (TUYA_* di .env).'], 503);
        }

        try {
            return response()->json(['url' => $this->tuya->allocateStreamUrl($cctv->device_id, $type), 'type' => $type]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => 'Gagal memuat siaran: ' . $e->getMessage()], 502);
        }
    }

    /**
     * Status online semua kamera yang terlihat di daftar: { cctv_id: bool }.
     */
    public function statusSemua(Request $request): JsonResponse
    {
        if (! $this->tuya->isConfigured()) {
            return response()->json(['message' => 'CCTV belum dikonfigurasi (TUYA_* di .env).'], 503);
        }

        try {
            $daftar = $this->tuya->devices();
        } catch (RuntimeException $e) {
            return response()->json(['message' => 'Gagal membaca status kamera: ' . $e->getMessage()], 502);
        }

        $online = Cctv::visibleTo($request->user())->where('aktif', true)->get(['id', 'device_id'])
            ->mapWithKeys(fn ($c) => [$c->id => $daftar['data'][$c->device_id]['online'] ?? false]);

        return response()->json(['online' => $online, 'diperiksa' => $daftar['diperiksa']]);
    }

    /**
     * Status satu kamera: online dan baterai (dipanggil berkala untuk kamera yang ditonton).
     */
    public function status(Request $request, Cctv $cctv): JsonResponse
    {
        $this->authorizeView($request, $cctv);

        if (! $this->tuya->isConfigured()) {
            return response()->json(['message' => 'CCTV belum dikonfigurasi (TUYA_* di .env).'], 503);
        }

        try {
            return response()->json($this->tuya->deviceStatus($cctv->device_id));
        } catch (RuntimeException $e) {
            return response()->json(['message' => 'Gagal membaca status kamera: ' . $e->getMessage()], 502);
        }
    }

    /**
     * Akun PPK hanya boleh membuka kamera di paket PPK-nya; kamera nonaktif tertutup bagi semua.
     */
    private function authorizeView(Request $request, Cctv $cctv): void
    {
        abort_unless(
            $cctv->aktif && Cctv::visibleTo($request->user())->whereKey($cctv->id)->exists(),
            403
        );
    }
}
