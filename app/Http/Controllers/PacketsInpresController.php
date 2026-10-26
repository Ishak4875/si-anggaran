<?php

namespace App\Http\Controllers;

use App\Models\Packet;
use App\Support\Satker;

class PacketsInpresController extends Controller
{
    private function authorize()
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * Daftar paket Inpres per satker, dikenali dari awalan kdpaket di config/inpres.php.
     */
    public function index()
    {
        $this->authorize();
        $names = Satker::getNamesMap();

        $data = [];
        foreach (config('inpres.prefixes', []) as $slug => $prefixes) {
            $packets = Packet::with('ppk')
                ->where(function ($q) use ($prefixes) {
                    foreach ($prefixes as $prefix) {
                        $q->orWhere('kdpaket', 'like', $prefix . '%');
                    }
                })
                ->orderBy('kdpaket')
                ->get();

            $pagu = (float) $packets->sum('pagu');
            $realisasi = (float) $packets->sum('realisasi');

            $data[$slug] = [
                'name' => $names[$slug] ?? $slug,
                'singkatan' => Satker::groups()[$slug]['singkatan'] ?? strtoupper($slug),
                'packets' => $packets,
                'pagu' => $pagu,
                'realisasi' => $realisasi,
                // Konvensi aplikasi: Keu = Σrealisasi/Σpagu; Fis = rata-rata real_fisik berbobot pagu
                'keu' => $pagu > 0 ? $realisasi / $pagu * 100 : null,
                'fis' => $pagu > 0 ? $packets->sum(fn ($p) => (float) $p->real_fisik * (float) $p->pagu) / $pagu : null,
                'tanpa_ppk' => $packets->whereNull('ppk_id')->count(),
            ];
        }

        return view('v_packets_inpres', ['data' => $data]);
    }
}
