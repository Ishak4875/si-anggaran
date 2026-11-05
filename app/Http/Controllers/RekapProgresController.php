<?php

namespace App\Http\Controllers;

use App\Models\Packet;
use App\Support\Satker;
use Illuminate\Database\Eloquent\Builder;

class RekapProgresController extends Controller
{
    private function authorize()
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * Rekap progres Paket Reguler dan Paket Inpres, per satker dan PPK.
     */
    public function index()
    {
        $this->authorize();

        $jenis = [
            'reguler' => ['judul' => 'Paket Reguler'] + $this->rekap(Packet::query()->reguler()),
            'inpres' => ['judul' => 'Paket Inpres'] + $this->rekap(Packet::query()->inpres()),
        ];

        return view('v_rekap_progres', [
            'jenis' => $jenis,
            'total' => $this->gabung([$jenis['reguler']['total'], $jenis['inpres']['total']]),
        ]);
    }

    /**
     * Agregasi satu jenis paket: daftar satker (masing-masing dengan baris PPK) dan totalnya.
     */
    private function rekap(Builder $query): array
    {
        $baris = $query
            ->leftJoin('ppks', 'packets.ppk_id', '=', 'ppks.id')
            ->selectRaw('packets.satker_group as satker_group')
            ->selectRaw('MAX(ppks.jabatan) as jabatan')
            ->selectRaw('MAX(ppks.urutan) as urutan')
            ->selectRaw('COUNT(*) as jml')
            ->selectRaw('SUM(packets.pagu) as pagu')
            ->selectRaw('SUM(packets.realisasi) as realisasi')
            ->selectRaw('SUM(packets.real_fisik * packets.pagu) as fnum')
            ->selectRaw('SUM(CASE WHEN packets.real_fisik IS NOT NULL THEN packets.pagu ELSE 0 END) as fden')
            ->groupBy('packets.satker_group', 'packets.ppk_id')
            ->get()
            ->groupBy('satker_group');

        $satker = [];
        foreach (Satker::groups() as $slug => $group) {
            if (! $baris->has($slug)) {
                continue;
            }

            // Urut sesuai Kelola PPK; paket tanpa PPK di paling bawah.
            $ppk = $baris[$slug]
                ->sortBy(fn ($r) => [$r->jabatan === null ? 1 : 0, $r->urutan ?? 999, $r->jabatan])
                ->map(fn ($r) => ['nama' => $r->jabatan ?? 'Belum ada PPK'] + $this->hitung(
                    (int) $r->jml, (float) $r->pagu, (float) $r->realisasi, (float) $r->fnum, (float) $r->fden
                ))
                ->values()
                ->all();

            $satker[] = ['nama' => $group['nama'], 'ppk' => $ppk, 'total' => $this->gabung($ppk)];
        }

        return ['satker' => $satker, 'total' => $this->gabung(array_column($satker, 'total'))];
    }

    private function gabung(array $baris): array
    {
        return $this->hitung(
            array_sum(array_column($baris, 'jml')),
            array_sum(array_column($baris, 'pagu')),
            array_sum(array_column($baris, 'realisasi')),
            array_sum(array_column($baris, 'fnum')),
            array_sum(array_column($baris, 'fden')),
        );
    }

    /**
     * Konvensi aplikasi: Keu = Σrealisasi/Σpagu; Fis = rata-rata real_fisik berbobot pagu
     * (real_fisik sudah dalam persen, jadi tidak dikali 100).
     */
    private function hitung(int $jml, float $pagu, float $realisasi, float $fnum, float $fden): array
    {
        return [
            'jml' => $jml,
            'pagu' => $pagu,
            'realisasi' => $realisasi,
            'fnum' => $fnum,
            'fden' => $fden,
            'keu' => $pagu > 0 ? $realisasi / $pagu * 100 : null,
            'fis' => $fden > 0 ? $fnum / $fden : null,
        ];
    }
}
