<?php

namespace App\Services;

use App\Models\Packet;
use App\Support\Satker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PacketSyncService
{
    /**
     * Ambil seluruh data paket dari API lalu simpan (snapshot penuh) ke DB.
     *
     * @return array{total:int, per_satker:array<string,int>}
     */
    public function sync(): array
    {
        $rows = $this->fetch();

        // Pertahankan penetapan PPK (ppk_id) yang sudah ada — dikunci pada kdpaket
        // agar tidak hilang saat snapshot penuh (delete + insert ulang).
        $prevPpk = Packet::whereNotNull('ppk_id')
            ->pluck('ppk_id', 'kdpaket')
            ->all();

        $now     = now();
        $records = [];

        foreach ($rows as $row) {
            $slug = Satker::slugForKdsatker($row['kdsatker'] ?? null);

            // Lewati paket yang kdsatker-nya tidak termasuk salah satu satker terdaftar
            // (mis. 694101) agar tidak ikut tersimpan.
            if ($slug === 'lainnya') {
                continue;
            }

            $kdpaket = $row['kdpaket'] ?? null;

            $records[] = [
                'tahun'            => $row['tahun'] ?? null,
                'kdsatker'         => $row['kdsatker'] ?? null,
                'satker_group'     => $slug,
                'ppk_id'           => $prevPpk[$kdpaket] ?? null,
                'kdprogram'        => $row['kdprogram'] ?? null,
                'kdgiat'           => $row['kdgiat'] ?? null,
                'kdoutput'         => $row['kdoutput'] ?? null,
                'kdsoutput'        => $row['kdsoutput'] ?? null,
                'kdkmpnen'         => $row['kdkmpnen'] ?? null,
                'kdskmpnen'        => $row['kdskmpnen'] ?? null,
                'kdpaket'          => $row['kdpaket'] ?? null,
                'nmpaket'          => $row['nmpaket'] ?? null,
                'vol'              => $row['vol'] ?? null,
                'sat'              => $row['sat'] ?? null,
                'pagu'             => $this->num($row['pagu'] ?? null),
                'pagu51'           => $this->num($row['pagu51'] ?? null),
                'pagu52'           => $this->num($row['pagu52'] ?? null),
                'pagu53'           => $this->num($row['pagu53'] ?? null),
                'realisasi'        => $this->num($row['real'] ?? null),
                'real_51'          => $this->num($row['real_51'] ?? null),
                'real_52'          => $this->num($row['real_52'] ?? null),
                'real_53'          => $this->num($row['real_53'] ?? null),
                'progres_keuangan' => $this->num($row['progres_keuangan'] ?? null),
                'real_fisik'       => $this->num($row['real_fisik'] ?? null),
                'raw'              => json_encode($row, JSON_UNESCAPED_UNICODE),
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        // Snapshot penuh: kosongkan lalu isi ulang dalam satu transaksi.
        // Pakai delete() (DML), bukan truncate() — TRUNCATE memicu implicit
        // commit di MySQL sehingga transaksi terputus.
        DB::transaction(function () use ($records) {
            Packet::query()->delete();
            foreach (array_chunk($records, 100) as $chunk) {
                Packet::insert($chunk);
            }
        });

        $perSatker = [];
        foreach ($records as $r) {
            $slug = $r['satker_group'];
            $perSatker[$slug] = ($perSatker[$slug] ?? 0) + 1;
        }

        return [
            'total'      => count($records),
            'per_satker' => $perSatker,
        ];
    }

    /**
     * Panggil API balai_packet dan kembalikan array of object.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function fetch(): array
    {
        $baseUrl  = rtrim((string) config('services.sihka.url'), '/');
        $endpoint = (string) config('services.sihka.endpoint');
        $key      = (string) config('services.sihka.key');

        $response = Http::timeout(120)
            ->withHeaders(['x-key' => $key])
            ->acceptJson()
            ->get($baseUrl . $endpoint);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Gagal mengambil data API (HTTP ' . $response->status() . ').'
            );
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException('Format respons API tidak dikenali (bukan array).');
        }

        // Antisipasi bila API membungkus array di dalam key "data".
        if (isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }

        return $data;
    }

    /**
     * Ubah string angka dari API menjadi float; null bila kosong/non-numerik.
     */
    protected function num($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
