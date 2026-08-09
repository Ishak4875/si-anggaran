<?php

namespace Database\Seeders;

use App\Models\PaguRevision;
use Illuminate\Database\Seeder;

class PaguRevisionSeeder extends Seeder
{
    /**
     * Data awal riwayat revisi pagu per satker (nilai dalam ribu rupiah).
     */
    public function run(): void
    {
        $data = [
            [
                'urutan'     => 0,
                'tanggal'    => '2025-12-01',
                'keterangan' => 'Pagu Awal',
                'nilai'      => ['op' => 99837615, 'bendungan' => 23835000, 'balai' => 28153152, 'pjsa' => 137435000, 'pjpa' => 97825000],
            ],
            [
                'urutan'     => 1,
                'tanggal'    => '2025-12-29',
                'keterangan' => 'Revisi Pergeseran Direktif Presiden',
                'nilai'      => ['op' => 98568328, 'bendungan' => 19077183, 'balai' => 24913490, 'pjsa' => 109966193, 'pjpa' => 91684429],
            ],
            [
                'urutan'     => 2,
                'tanggal'    => '2026-02-03',
                'keterangan' => 'Efisiensi gaji petugas OP yang menjadi PPPK Paruh Waktu',
                'nilai'      => ['op' => 98111848, 'bendungan' => 19077183, 'balai' => 24913490, 'pjsa' => 109966193, 'pjpa' => 91684429],
            ],
            [
                'urutan'     => 3,
                'tanggal'    => '2026-04-22',
                'keterangan' => 'Efisiensi Perjalanan Dinas',
                'nilai'      => ['op' => 95031008, 'bendungan' => 18756391, 'balai' => 21168363, 'pjsa' => 109806059, 'pjpa' => 91502973],
            ],
            [
                'urutan'     => 4,
                'tanggal'    => '2026-06-03',
                'keterangan' => 'Penambahan Anggaran P3-TGAI',
                'nilai'      => ['op' => 234731008, 'bendungan' => 17454937, 'balai' => 21745867, 'pjsa' => 94314656, 'pjpa' => 82340766],
            ],
        ];

        foreach ($data as $row) {
            PaguRevision::updateOrCreate(
                ['keterangan' => $row['keterangan']],
                $row
            );
        }
    }
}
