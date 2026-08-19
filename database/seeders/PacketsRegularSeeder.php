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
                'kode_paket' => '03.694170.FC.7691.CBR.001.301.A',
                'nama_paket' => 'Desain Rehabilitasi Jaringan Irigasi DI. Mowila Kab. Konawe Selatan; 1 Dokumen; 1 Dokumen; NF; K; SYC',
                'satker' => 'balai',
                'pagu' => 1602796000,
            ],
            [
                'kode_paket' => '03.694170.FC.7691.CBR.001.301.B',
                'nama_paket' => 'Pemutakhiran Pemetaan Daerah Irigasi Kewenangan Pusat di Provinsi Sulawesi Tenggara; 1 Dokumen; 1 Dokumen; NF; K; SYC',
                'satker' => 'balai',
                'pagu' => 1764411000,
            ],
            [
                'kode_paket' => '03.694170.FC.7691.CBR.001.301.C',
                'nama_paket' => 'Penyusunan Dokumen Kesiapan Peningkatan Jaringan Irigasi DI. Watumokala Kab. Konawe Selatan; 1 Dokumen; 1 Dokumen; NF; K; SYC',
                'satker' => 'balai',
                'pagu' => 1597768000,
            ],
            [
                'kode_paket' => '03.694170.FC.7692.CBR.001.301.A',
                'nama_paket' => 'Penyusunan Dokumen Lingkungan Kegiatan Pembangunan Pengaman Pantai Kawasan Anaiwoi - Kampung Bajo Kab. Kolaka; 1 Dokumen; 1 Dokumen; NF; K; SYC',
                'satker' => 'balai',
                'pagu' => 846600000,
            ],
            [
                'kode_paket' => '03.694170.FC.7694.CBR.001.301.A',
                'nama_paket' => 'Desain Rehabilitasi Air Baku Tolihe Kab. Konawe Selatan; 1 Dokumen; 1 Dokumen; NF; K; SYC',
                'satker' => 'balai',
                'pagu' => 970140000,
            ],
            [
                'kode_paket' => 'OPA-001',
                'nama_paket' => 'Pembangunan Jaringan Irigasi DI Laiba Kab. Muna',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-002',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Jaringan Irigasi DI Laiba Kab. Muna',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-003',
                'nama_paket' => 'Rehabilitasi Jaringan Irigasi D.I Walay Kab. Konawe (Tahap III)',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-004',
                'nama_paket' => 'Supervisi Konstruksi Rehabilitasi Jaringan Irigasi D.I Walay Kab. Konawe (Tahap III)',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-005',
                'nama_paket' => 'Rehabilitasi Jaringan Transmisi Air Baku Kota Baubau',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-006',
                'nama_paket' => 'Supervisi Konstruksi Rehabilitasi Jaringan Transmisi Air Baku Kota Baubau',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-007',
                'nama_paket' => 'Rehabilitasi Embung Ulu Benua Kab. Konawe',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-001',
                'nama_paket' => 'Pengendalian Banjir Sungai Lasusua (Lanjutan) Kab. Kolaka Utara',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-002',
                'nama_paket' => 'Supervisi Konstruksi Pengendalian Banjir Sungai Lasusua (Lanjutan) Kab. Kolaka Utara',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-003',
                'nama_paket' => 'Pengendalian Banjir Sungai Lasolo (Lanjutan) Kab. Konawe Utara',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-004',
                'nama_paket' => 'Supervisi Konstruksi Pengendalian Banjir Sungai Lasolo (Lanjutan) Kab. Konawe Utara',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-005',
                'nama_paket' => 'Pembangunan Pengaman Pantai Lasusua (Lanjutan) Kab. Kolaka Utara',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-006',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Pengaman Pantai Lasusua (Lanjutan) Kab. Kolaka Utara',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-007',
                'nama_paket' => 'Pembangunan Pengaman Pantai Kawasan Anaiwoi-Kampung Bajo Kab. Kolaka',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-008',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Pengaman Pantai Kawasan Anaiwoi-Kampung Bajo Kab. Kolaka',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-009',
                'nama_paket' => 'Pembangunan Pengaman Pantai Tondowolio (Lanjutan) Kab. Kolaka',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-010',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Pengaman Pantai Tondowolio (Lanjutan) Kab. Kolaka',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-011',
                'nama_paket' => 'Pembangunan Pengaman Pantai Raha (Lanjutan-Tahap III) Kab. Muna',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-012',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Pengaman Pantai Raha (Lanjutan-Tahap III) Kab. Muna',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-013',
                'nama_paket' => 'Pengendalian Banjir Sungai Konaweha Kab. Konawe',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-014',
                'nama_paket' => 'Supervisi Konstruksi Pengendalian Banjir Sungai Konaweha Kab. Konawe',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-015',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Tanggul Banjir Sungai Wanggu Kota Kendari (Lanjutan)',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJPA-016',
                'nama_paket' => 'Pembangunan Tanggul Banjir Sungai Wanggu Kota Kendari (Lanjutan)',
                'satker' => 'pjpa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJSA-001',
                'nama_paket' => 'Peningkatan Fungsi Tampungan Bendungan Ladongi untuk mendukung Ketahanan Pangan di Kabupaten Kolaka Timur',
                'satker' => 'pjsa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJSA-002',
                'nama_paket' => 'Supervisi Konstruksi Peningkatan Fungsi Tampungan Bendungan Ladongi untuk mendukung Ketahanan Pangan di Kabupaten Kolaka Timur',
                'satker' => 'pjsa',
                'pagu' => 0,
            ],
        ];

        foreach ($data as $packet) {
            PacketsRegular::create($packet);
        }
    }
}