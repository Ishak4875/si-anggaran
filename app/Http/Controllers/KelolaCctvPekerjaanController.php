<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use App\Models\Packet;
use App\Models\Ppk;
use App\Models\User;
use App\Support\Satker;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

/**
 * Kelola CCTV Pekerjaan: pemetaan kamera per PPK → paket pekerjaan. Akun role "ppk"
 * hanya melihat kamera di paket PPK-nya (Cctv::scopeVisibleTo). Akses: Super Admin.
 * Simpan/hapus memakai logika Kelola CCTV, ditambah cek paket milik PPK yang dipilih.
 */
class KelolaCctvPekerjaanController extends KelolaCctvController
{
    protected string $routeIndex = 'kelola-cctv-pekerjaan.index';

    public function index(): View
    {
        $tuyaError = null;
        try {
            $perangkat = $this->tuya->isConfigured() ? $this->tuya->devices()['data'] : [];
        } catch (RuntimeException $e) {
            $perangkat = null;
            $tuyaError = $e->getMessage();
        }

        $cctvs = Cctv::with('packet')->orderBy('nama')->get();
        $paket = Packet::orderBy('kdpaket')->get(['kdpaket', 'nmpaket', 'satker_group', 'ppk_id']);
        $ppks = Ppk::orderBy('jabatan')->get();
        $akun = User::where('role', 'ppk')->orderBy('name')->get()->groupBy('ppk_id');

        // Satker → PPK, masing-masing dengan paketnya dan kamera per paket
        $kameraPerPaket = $cctvs->groupBy('kdpaket');
        $paketPerPpk = $paket->groupBy('ppk_id');
        $kelompok = [];
        foreach (Satker::groups() as $slug => $g) {
            $kelompok[$slug] = $ppks->where('satker_group', $slug)->map(fn (Ppk $p) => [
                'ppk' => $p,
                'akun' => $akun[$p->id] ?? collect(),
                'paket' => $paketPerPpk[$p->id] ?? collect(),
                'jumlahKamera' => ($paketPerPpk[$p->id] ?? collect())
                    ->sum(fn ($pk) => count($kameraPerPaket[$pk->kdpaket] ?? [])),
            ])->values();
        }

        // Kamera yang belum bisa dilihat akun PPK mana pun
        $tanpaPpk = $cctvs->filter(fn (Cctv $c) => $c->packet && ! $c->packet->ppk_id);
        $paketHilang = $cctvs->filter(fn (Cctv $c) => ! $c->packet);

        return view('v_kelola_cctv_pekerjaan', [
            'kelompok' => $kelompok,
            'kameraPerPaket' => $kameraPerPaket,
            'tanpaPpk' => $tanpaPpk,
            'paketHilang' => $paketHilang,
            'perangkat' => $perangkat,
            'tuyaError' => $tuyaError,
            'dipakai' => $cctvs->pluck('nama', 'device_id'),
            'namaSatker' => Satker::getNamesMap(),
            'jumlahKamera' => $cctvs->count(),
        ]);
    }

    protected function validated(Request $request, ?Cctv $cctv = null): array
    {
        $request->validate([
            'ppk_id' => ['required', 'integer', 'exists:ppks,id'],
        ]);

        $data = parent::validated($request, $cctv);

        // Paket harus milik PPK yang dipilih (dicek ulang di server, bukan hanya di dropdown)
        $milikPpk = Packet::where('kdpaket', $data['kdpaket'])
            ->where('ppk_id', $request->integer('ppk_id'))
            ->exists();
        if (! $milikPpk) {
            throw ValidationException::withMessages([
                'kdpaket' => 'Paket yang dipilih bukan paket milik PPK tersebut.',
            ]);
        }

        return $data;
    }
}
