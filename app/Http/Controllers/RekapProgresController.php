<?php

namespace App\Http\Controllers;

use App\Models\DitjenProgres;
use App\Models\Packet;
use App\Support\Satker;
use App\Support\StatusLaporan;
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
     * Rekap progres Paket Reguler dan Paket Inpres, masing-masing berformat tabel
     * "Progres per PPK & Peringkat" di dashboard. Peringkat dihitung per jenis paket.
     */
    public function index()
    {
        $this->authorize();

        $ditjen = DitjenProgres::current();

        return view('v_rekap_progres', [
            'jenis' => [
                'reguler' => ['judul' => 'Paket Reguler', 'label' => 'Reguler'] + $this->rekap(Packet::query()->reguler()),
                'inpres' => ['judul' => 'Paket Inpres', 'label' => 'Inpres'] + $this->rekap(Packet::query()->inpres()),
            ],
            'ditjen' => ['keu' => $ditjen->keu, 'fis' => $ditjen->fis],
            'statusJam' => StatusLaporan::dariSync(Packet::max('updated_at')),
        ]);
    }

    /**
     * Agregasi satu jenis paket: satker yang punya paket (dengan baris PPK), peringkat, dan total.
     */
    private function rekap(Builder $query): array
    {
        $baris = $query
            ->leftJoin('ppks', 'packets.ppk_id', '=', 'ppks.id')
            ->selectRaw('packets.satker_group as satker_group')
            ->selectRaw('packets.ppk_id as ppk_id')
            ->selectRaw('MAX(ppks.jabatan) as jabatan')
            ->selectRaw('MAX(ppks.nama) as pejabat')
            ->selectRaw('MAX(ppks.urutan) as urutan')
            ->selectRaw('SUM(packets.pagu) as pagu')
            ->selectRaw('SUM(packets.realisasi) as realisasi')
            ->selectRaw('SUM(packets.real_fisik * packets.pagu) as fnum')
            ->selectRaw('SUM(CASE WHEN packets.real_fisik IS NOT NULL THEN packets.pagu ELSE 0 END) as fden')
            ->groupBy('packets.satker_group', 'packets.ppk_id')
            ->get()
            ->groupBy('satker_group');

        $groups = Satker::groups();
        $satkers = [];
        foreach ($this->urutanSatker() as $slug) {
            if (! $baris->has($slug)) {
                continue;
            }

            // Urut sesuai Kelola PPK; paket tanpa PPK di paling bawah.
            $ppks = $baris[$slug]
                ->sortBy(fn ($r) => [$r->jabatan === null ? 1 : 0, $r->urutan ?? 999, $r->jabatan])
                ->map(fn ($r) => [
                    'kunci' => $slug . '|' . ($r->ppk_id ?? 'none'),
                    'ppk' => $r->jabatan ?? '(Belum ada PPK)',
                    'pejabat' => $r->pejabat,
                ] + $this->hitung((float) $r->pagu, (float) $r->realisasi, (float) $r->fnum, (float) $r->fden))
                ->values()
                ->all();

            $satkers[] = [
                'kunci' => $slug,
                'nama' => $groups[$slug]['nama'],
                'singkatan' => $groups[$slug]['singkatan'],
                'ppks' => $ppks,
            ] + $this->gabung($ppks);
        }

        $rankSatker = $this->rank($satkers);
        $rankPpk = $this->rank(array_merge(...array_column($satkers, 'ppks') ?: [[]]));
        foreach ($satkers as &$s) {
            $s['rank'] = $rankSatker[$s['kunci']];
            foreach ($s['ppks'] as &$p) {
                $p['rank'] = $rankPpk[$p['kunci']];
            }
            unset($p);
        }
        unset($s);

        return ['satkers' => $satkers, 'total' => $this->gabung($satkers)];
    }

    /**
     * Urutan sama dengan tabel dashboard: posisi balai dan op ditukar dari urutan config.
     */
    private function urutanSatker(): array
    {
        $urutan = array_keys(Satker::groups());
        $iBalai = array_search('balai', $urutan);
        $iOp = array_search('op', $urutan);
        if ($iBalai !== false && $iOp !== false) {
            [$urutan[$iBalai], $urutan[$iOp]] = [$urutan[$iOp], $urutan[$iBalai]];
        }

        return $urutan;
    }

    /**
     * Peringkat menurut Keu % menurun. Nilai Keu yang sama mendapat peringkat yang sama,
     * karena paket Inpres banyak yang masih 0%.
     *
     * @return array<string,int> [kunci => rank]
     */
    private function rank(array $items): array
    {
        $urut = collect($items)->sortByDesc(fn ($it) => $it['keu'] ?? -1)->values();

        $rank = [];
        foreach ($urut as $i => $it) {
            $sebelum = $urut[$i - 1] ?? null;
            $rank[$it['kunci']] = $sebelum && $sebelum['keu'] === $it['keu'] ? $rank[$sebelum['kunci']] : $i + 1;
        }

        return $rank;
    }

    private function gabung(array $baris): array
    {
        return $this->hitung(
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
    private function hitung(float $pagu, float $realisasi, float $fnum, float $fden): array
    {
        return [
            'pagu' => $pagu,
            'realisasi' => $realisasi,
            'fnum' => $fnum,
            'fden' => $fden,
            'keu' => $pagu > 0 ? $realisasi / $pagu * 100 : null,
            'fis' => $fden > 0 ? $fnum / $fden : null,
        ];
    }
}
