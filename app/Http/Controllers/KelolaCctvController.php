<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use App\Models\Packet;
use App\Services\TuyaCameraService;
use App\Support\Satker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

/**
 * Kelola CCTV: pasang kamera Tuya ke paket (satu paket bisa banyak kamera),
 * pindahkan ke paket lain, atau hapus. Akses: KPISDA & Super Admin (gate kelola-cctv).
 */
class KelolaCctvController extends Controller
{
    public function __construct(private TuyaCameraService $tuya)
    {
    }

    public function index(): View
    {
        $cctvs = Cctv::with('packet.ppk')->orderBy('nama')->get();

        // Kamera yang tertaut ke proyek Tuya; null bila Tuya tidak bisa dihubungi
        $tuyaError = null;
        try {
            $perangkat = $this->tuya->isConfigured() ? $this->tuya->devices()['data'] : [];
        } catch (RuntimeException $e) {
            $perangkat = null;
            $tuyaError = $e->getMessage();
        }

        $paket = Packet::with('ppk')->orderBy('satker_group')->orderBy('kdpaket')
            ->get(['kdpaket', 'nmpaket', 'satker_group', 'ppk_id']);

        return view('v_kelola_cctv', [
            'cctvs' => $cctvs,
            'perangkat' => $perangkat,
            'tuyaError' => $tuyaError,
            'paket' => $paket,
            'namaSatker' => Satker::getNamesMap(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Cctv::create($this->validated($request));

        return redirect()->route('kelola-cctv.index')->with('status', 'Kamera berhasil ditambahkan.');
    }

    public function update(Request $request, Cctv $cctv): RedirectResponse
    {
        $cctv->update($this->validated($request, $cctv));

        return redirect()->route('kelola-cctv.index')->with('status', 'Kamera berhasil diperbarui.');
    }

    public function destroy(Cctv $cctv): RedirectResponse
    {
        $cctv->delete();

        return redirect()->route('kelola-cctv.index')->with('status', 'Kamera berhasil dihapus.');
    }

    private function validated(Request $request, ?Cctv $cctv = null): array
    {
        $deviceRules = ['required', 'string', 'max:64', Rule::unique('cctvs', 'device_id')->ignore($cctv?->id)];

        // Bila daftar kamera Tuya bisa dibaca, Device ID harus salah satu kamera yang tertaut
        try {
            $ids = array_keys($this->tuya->devices()['data']);
            if ($ids) {
                $deviceRules[] = Rule::in($ids);
            }
        } catch (RuntimeException) {
            // Tuya tidak terjangkau: terima Device ID apa adanya
        }

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'device_id' => $deviceRules,
            'kdpaket' => ['required', 'string', Rule::exists('packets', 'kdpaket')],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ], [
            'device_id.unique' => 'Kamera ini sudah terpasang di paket lain. Pindahkan lewat tombol Perbarui pada kamera tersebut.',
            'device_id.in' => 'Kamera tidak ditemukan di proyek Tuya. Pastikan kamera sudah dipasangkan di aplikasi Smart Life akun yang tertaut.',
            'kdpaket.exists' => 'Kode paket tidak ditemukan di data paket.',
        ]);

        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }
}
