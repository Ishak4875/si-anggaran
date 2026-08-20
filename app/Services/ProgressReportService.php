<?php

namespace App\Services;

use App\Models\DitjenProgres;
use App\Models\Packet;
use App\Support\Satker;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProgressReportService
{
    /**
     * Build progress report data structure
     */
    public function build(): array
    {
        $ditjen = DitjenProgres::current();
        $satkerData = $this->buildSatkerData();
        $ppkData = $this->buildPpkData();
        $bottomPpk = $this->getBottomPerformer($ppkData, $ditjen->keu);

        return [
            'ditjen' => [
                'keu' => $ditjen->keu,
                'fis' => $ditjen->fis,
            ],
            'bws' => $this->calculateBwsAggregate($satkerData),
            'satkers' => $satkerData,
            'ppks' => $ppkData,
            'bottom_ppk' => $bottomPpk,
            'timestamp' => $this->getStatusTimestamp(),
        ];
    }

    /**
     * Build satker-level progress data with rankings
     */
    private function buildSatkerData(): array
    {
        $aggregates = Packet::selectRaw('satker_group')
            ->selectRaw('COUNT(*) as jml')
            ->selectRaw('SUM(pagu) as pagu')
            ->selectRaw('SUM(realisasi) as realisasi')
            ->selectRaw('SUM(CASE WHEN real_fisik IS NOT NULL THEN real_fisik * pagu ELSE 0 END) as fisik_num')
            ->selectRaw('SUM(CASE WHEN real_fisik IS NOT NULL THEN pagu ELSE 0 END) as fisik_den')
            ->groupBy('satker_group')
            ->get()
            ->keyBy('satker_group');

        $satkers = [];
        foreach (Satker::SLUGS as $slug) {
            if (!isset($aggregates[$slug])) {
                continue;
            }

            $agg = $aggregates[$slug];
            $keu = $agg->pagu > 0 ? ($agg->realisasi / $agg->pagu) * 100 : 0;
            $fis = $agg->fisik_den > 0 ? ($agg->fisik_num / $agg->fisik_den) : 0;

            $satkers[$slug] = [
                'slug' => $slug,
                'nama' => Satker::nama($slug),
                'singkatan' => Satker::singkatan($slug),
                'jml' => (int) $agg->jml,
                'pagu' => (float) $agg->pagu,
                'realisasi' => (float) $agg->realisasi,
                'keu' => round($keu, 2),
                'fis' => round($fis, 2),
            ];
        }

        // Rank by Keuangan descending
        usort($satkers, fn($a, $b) => $b['keu'] <=> $a['keu']);
        foreach ($satkers as $i => &$s) {
            $s['rank'] = $i + 1;
        }

        return $satkers;
    }

    /**
     * Build PPK-level progress data with rankings
     */
    private function buildPpkData(): array
    {
        $aggregates = Packet::leftJoin('ppks', 'packets.ppk_id', '=', 'ppks.id')
            ->selectRaw('packets.satker_group')
            ->selectRaw('packets.ppk_id')
            ->selectRaw('MAX(ppks.nama) as ppk_nama')
            ->selectRaw('MAX(ppks.jabatan) as ppk_jabatan')
            ->selectRaw('COUNT(packets.id) as jml')
            ->selectRaw('SUM(packets.pagu) as pagu')
            ->selectRaw('SUM(packets.realisasi) as realisasi')
            ->selectRaw('SUM(CASE WHEN packets.real_fisik IS NOT NULL THEN packets.real_fisik * packets.pagu ELSE 0 END) as fisik_num')
            ->selectRaw('SUM(CASE WHEN packets.real_fisik IS NOT NULL THEN packets.pagu ELSE 0 END) as fisik_den')
            ->groupBy('packets.satker_group', 'packets.ppk_id')
            ->get();

        $ppks = [];
        foreach ($aggregates as $agg) {
            $keu = $agg->pagu > 0 ? ($agg->realisasi / $agg->pagu) * 100 : 0;
            $fis = $agg->fisik_den > 0 ? ($agg->fisik_num / $agg->fisik_den) : 0;

            $ppks[] = [
                'ppk_id' => $agg->ppk_id,
                'nama' => $agg->ppk_nama ?: '—',
                'jabatan' => $agg->ppk_jabatan ?: '—',
                'satker' => $agg->satker_group,
                'jml' => (int) $agg->jml,
                'pagu' => (float) $agg->pagu,
                'realisasi' => (float) $agg->realisasi,
                'keu' => $keu !== null ? round($keu, 2) : null,
                'fis' => $fis !== null ? round($fis, 2) : 0,
            ];
        }

        // Rank by Keuangan descending (nulls at end)
        usort($ppks, function ($a, $b) {
            if ($a['keu'] === null && $b['keu'] === null) return 0;
            if ($a['keu'] === null) return 1;
            if ($b['keu'] === null) return -1;
            return $b['keu'] <=> $a['keu'];
        });

        foreach ($ppks as $i => &$p) {
            $p['rank'] = $i + 1;
        }

        return $ppks;
    }

    /**
     * Calculate BWS-level aggregates
     */
    private function calculateBwsAggregate(array $satkers): array
    {
        $pagu = 0;
        $realisasi = 0;
        $fisik_num = 0;
        $fisik_den = 0;

        foreach ($satkers as $s) {
            $pagu += $s['pagu'];
            $realisasi += $s['realisasi'];
        }

        // Recalculate fisik from packets directly for accuracy
        $packets = Packet::selectRaw('SUM(CASE WHEN real_fisik IS NOT NULL THEN real_fisik * pagu ELSE 0 END) as fisik_num')
            ->selectRaw('SUM(CASE WHEN real_fisik IS NOT NULL THEN pagu ELSE 0 END) as fisik_den')
            ->first();

        $fisik_num = $packets->fisik_num ?? 0;
        $fisik_den = $packets->fisik_den ?? 0;

        $keu = $pagu > 0 ? ($realisasi / $pagu) * 100 : 0;
        $fis = $fisik_den > 0 ? ($fisik_num / $fisik_den) : 0;

        return [
            'pagu' => (float) $pagu,
            'realisasi' => (float) $realisasi,
            'keu' => round($keu, 2),
            'fis' => round($fis, 2),
        ];
    }

    /**
     * Get PPK with lowest Keuangan (bottom performer)
     */
    private function getBottomPerformer(array $ppks, ?float $ditjenKeu): ?array
    {
        $below = array_filter($ppks, fn($p) => $p['keu'] !== null && $p['keu'] < $ditjenKeu);

        if (empty($below)) {
            return null;
        }

        // Sort by keu ascending (lowest first)
        usort($below, fn($a, $b) => $a['keu'] <=> $b['keu']);

        return reset($below) ?: null;
    }

    /**
     * Get current status timestamp from database
     * Falls back to current time if no data
     */
    private function getStatusTimestamp(): string
    {
        // Try to get from Packet with latest timestamp
        $packet = Packet::latest('updated_at')->first();

        if ($packet) {
            $date = $packet->updated_at->setTimezone('Asia/Makassar');
            return $date->format('d M Y') . ' ; ' . $date->format('H:i') . ' WITA';
        }

        // Fallback to current time
        $now = now()->setTimezone('Asia/Makassar');
        return $now->format('d M Y') . ' ; ' . $now->format('H:i') . ' WITA';
    }
}
