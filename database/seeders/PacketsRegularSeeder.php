<?php

namespace Database\Seeders;

use App\Models\PacketsRegular;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PacketsRegularSeeder extends Seeder
{
    public function run(): void
    {
        PacketsRegular::truncate();

        $data = [
            [
                'kode_paket' => 'BAL-001',
                'nama_paket' => 'PPK Perc. - Desain Rehabilitasi Jaringan Irigasi DI. Mowila Kab. Konawe Selatan',
                'satker' => 'balai',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'BAL-002',
                'nama_paket' => 'PPK Perc. - Pemutakhiran Pemetaan Daerah Irigasi Kewenangan Pusat di Provinsi Sulawesi Tenggara',
                'satker' => 'balai',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'BAL-003',
                'nama_paket' => 'PPK Perc. - Penyusunan Dokumen Kesiapan Peningkatan Jaringan Irigasi DI. Watumokala Kab. Konawe Selatan',
                'satker' => 'balai',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'BAL-004',
                'nama_paket' => 'PPK Perc. - Penyusunan Dokumen Lingkungan Kegiatan Pembangunan Pengaman Pantai Kawasan Anaiwoi - Kampung Bajo Kab. Kolaka',
                'satker' => 'balai',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'BAL-005',
                'nama_paket' => 'PPK Perc. - Desain Rehabilitasi Air Baku Tolihe Kab. Konawe Selatan',
                'satker' => 'balai',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-001',
                'nama_paket' => 'PPK IRWA 1 - Pembangunan Jaringan Irigasi DI Laiba Kab. Muna',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-002',
                'nama_paket' => 'PPK IRWA 1 - Supervisi Konstruksi Pembangunan Jaringan Irigasi DI Laiba Kab. Muna',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-003',
                'nama_paket' => 'PPK IRWA 3 - Rehabilitasi Jaringan Irigasi D.I Walay Kab. Konawe (Tahap III)',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-004',
                'nama_paket' => 'PPK IRWA 3 - Supervisi Konstruksi Rehabilitasi Jaringan Irigasi D.I Walay Kab. Konawe (Tahap III)',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-005',
                'nama_paket' => 'PPK ATAB 2 - Rehabilitasi Jaringan Transmisi Air Baku Kota Baubau',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-006',
                'nama_paket' => 'PPK ATAB 2 - Supervisi Konstruksi Rehabilitasi Jaringan Transmisi Air Baku Kota Baubau',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-007',
                'nama_paket' => 'PPK ATAB 3 - Rehabilitasi Embung Ulu Benua Kab. Konawe',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-001',
                'nama_paket' => 'PPK SUPAN 1 - Pengendalian Banjir Sungai Lasusua (Lanjutan) Kab. Kolaka Utara',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-002',
                'nama_paket' => 'PPK SUPAN 1 - Supervisi Konstruksi Pengendalian Banjir Sungai Lasusua (Lanjutan) Kab. Kolaka Utara',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-003',
                'nama_paket' => 'PPK SUPAN 2 - Pengendalian Banjir Sungai Lasolo (Lanjutan) Kab. Konawe Utara',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-004',
                'nama_paket' => 'PPK SUPAN 2 - Supervisi Konstruksi Pengendalian Banjir Sungai Lasolo (Lanjutan) Kab. Konawe Utara',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-005',
                'nama_paket' => 'PPK SUPAN 1 - Pembangunan Pengaman Pantai Lasusua (Lanjutan) Kab. Kolaka Utara',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-006',
                'nama_paket' => 'PPK SUPAN 1 - Supervisi Konstruksi Pembangunan Pengaman Pantai Lasusua (Lanjutan) Kab. Kolaka Utara',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-007',
                'nama_paket' => 'PPK SUPAN 2 - Pembangunan Pengaman Pantai Kawasan Anaiwoi-Kampung Bajo Kab. Kolaka',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-008',
                'nama_paket' => 'PPK SUPAN 2 - Supervisi Konstruksi Pembangunan Pengaman Pantai Kawasan Anaiwoi-Kampung Bajo Kab. Kolaka',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-009',
                'nama_paket' => 'PPK SUPAN 2 - Pembangunan Pengaman Pantai Tondowolio (Lanjutan) Kab. Kolaka',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-010',
                'nama_paket' => 'PPK SUPAN 2 - Supervisi Konstruksi Pembangunan Pengaman Pantai Tondowolio (Lanjutan) Kab. Kolaka',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-011',
                'nama_paket' => 'PPK SUPAN 1 - Pembangunan Pengaman Pantai Raha (Lanjutan-Tahap III) Kab. Muna',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-012',
                'nama_paket' => 'PPK SUPAN 1 - Supervisi Konstruksi Pembangunan Pengaman Pantai Raha (Lanjutan-Tahap III) Kab. Muna',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-013',
                'nama_paket' => 'PPK SUPAN 2 - Pengendalian Banjir Sungai Konaweha Kab. Konawe',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-014',
                'nama_paket' => 'PPK SUPAN 2 - Supervisi Konstruksi Pengendalian Banjir Sungai Konaweha Kab. Konawe',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-015',
                'nama_paket' => 'PPK SUPAN 2 - Supervisi Konstruksi Pembangunan Tanggul Banjir Sungai Wanggu Kota Kendari (Lanjutan)',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-016',
                'nama_paket' => 'PPK SUPAN 2 - Pembangunan Tanggul Banjir Sungai Wanggu Kota Kendari (Lanjutan)',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJSA-001',
                'nama_paket' => 'PPK BEND 1. - Peningkatan Fungsi Tampungan Bendungan Ladongi untuk mendukung Ketahanan Pangan di Kabupaten Kolaka Timur',
                'satker' => 'pjsa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJSA-002',
                'nama_paket' => 'PPK PERC. BEND. - Supervisi Konstruksi Peningkatan Fungsi Tampungan Bendungan Ladongi untuk mendukung Ketahanan Pangan di Kabupaten Kolaka Timur',
                'satker' => 'pjsa',
                'pagu' => 0,
            ],
        ];

        foreach ($data as $packet) {
            PacketsRegular::create($packet);
        }
    }
}