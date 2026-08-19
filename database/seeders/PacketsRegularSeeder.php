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
                'kode_paket' => 'OPA-101',
                'nama_paket' => 'Pembangunan Jaringan Irigasi DI Laiba Kab. Muna',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-102',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Jaringan Irigasi DI Laiba Kab. Muna',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-103',
                'nama_paket' => 'Rehabilitasi Jaringan Irigasi D.I Walay Kab. Konawe (Tahap III)',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-104',
                'nama_paket' => 'Supervisi Konstruksi Rehabilitasi Jaringan Irigasi D.I Walay Kab. Konawe (Tahap III)',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-105',
                'nama_paket' => 'Rehabilitasi Jaringan Transmisi Air Baku Kota Baubau',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-106',
                'nama_paket' => 'Supervisi Konstruksi Rehabilitasi Jaringan Transmisi Air Baku Kota Baubau',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'OPA-107',
                'nama_paket' => 'Rehabilitasi Embung Ulu Benua Kab. Konawe',
                'satker' => 'op',
                'pagu' => 0,
            ],
            [
                'kode_paket' => '03.694243.FC.7692.CBS.001.100.A',
                'nama_paket' => 'Supervisi Konstruksi Pengendalian Banjir Sungai Lasusua (Lanjutan) Kab. Kolaka Utara; 1 Dokumen; 1 Dokumen; NF; K; SYC',
                'satker' => 'pjsa',
                'pagu' => 630461000,
            ],
            [
                'kode_paket' => 'PJSA-101',
                'nama_paket' => 'Supervisi Konstruksi Pengendalian Banjir Sungai Lasusua (Lanjutan) Kab. Kolaka Utara',
                'satker' => 'pjsa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJSA-102',
                'nama_paket' => 'Pengendalian Banjir Sungai Lasolo (Lanjutan) Kab. Konawe Utara',
                'satker' => 'pjsa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJSA-103',
                'nama_paket' => 'Supervisi Konstruksi Pengendalian Banjir Sungai Lasolo (Lanjutan) Kab. Konawe Utara',
                'satker' => 'pjsa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => '03.694243.FC.7692.CBS.003.103.A',
                'nama_paket' => 'Pembangunan Pengaman Pantai Lasusua (Lanjutan) Kab. Kolaka Utara; 0.5 Km; 5 Ha; F; K; SYC',
                'satker' => 'pjsa',
                'pagu' => 16063925000,
            ],
            [
                'kode_paket' => '03.694243.FC.7692.CBS.003.100.C',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Pengaman Pantai Tondowolio (Lanjutan) Kab. Kolaka; 1 Dokumen; 1 Dokumen; NF; K; SYC',
                'satker' => 'pjsa',
                'pagu' => 797865000,
            ],
            [
                'kode_paket' => '03.694243.FC.7692.CBS.003.100.B',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Pengaman Pantai Kawasan Anaiwoi-Kampung Bajo Kab. Kolaka; 1 Dokumen; 1 Dokumen; NF; K; SYC',
                'satker' => 'pjsa',
                'pagu' => 518400000,
            ],
            [
                'kode_paket' => 'PJSA-104',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Pengaman Pantai Kawasan Anaiwoi-Kampung Bajo Kab. Kolaka',
                'satker' => 'pjsa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJSA-105',
                'nama_paket' => 'Pembangunan Pengaman Pantai Tondowolio (Lanjutan) Kab. Kolaka',
                'satker' => 'pjsa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJSA-106',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Pengaman Pantai Tondowolio (Lanjutan) Kab. Kolaka',
                'satker' => 'pjsa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => '03.694243.FC.7692.CBS.003.103.D',
                'nama_paket' => 'Pembangunan Pengaman Pantai Raha (Lanjutan-Tahap III) Kab. Muna; 0.475 Km; 4.75 Ha; F; K; SYC',
                'satker' => 'pjsa',
                'pagu' => 16413438000,
            ],
            [
                'kode_paket' => 'PJSA-107',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Pengaman Pantai Raha (Lanjutan-Tahap III) Kab. Muna',
                'satker' => 'pjsa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => '03.694243.FC.7692.RBS.005.100.A',
                'nama_paket' => 'Supervisi Konstruksi Pengendalian Banjir Sungai Konaweha Kab. Konawe; 1 Dokumen; 1 Dokumen; NF; K; SYC',
                'satker' => 'pjsa',
                'pagu' => 599210000,
            ],
            [
                'kode_paket' => 'PJSA-108',
                'nama_paket' => 'Supervisi Konstruksi Pengendalian Banjir Sungai Konaweha Kab. Konawe',
                'satker' => 'pjsa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => 'PJSA-109',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Tanggul Banjir Sungai Wanggu Kota Kendari (Lanjutan)',
                'satker' => 'pjsa',
                'pagu' => 0,
            ],
            [
                'kode_paket' => '03.694243.FC.7692.CBS.001.100.C',
                'nama_paket' => 'Supervisi Konstruksi Pembangunan Tanggul Banjir Sungai Wanggu Kota Kendari (Lanjutan); 1 Dokumen; 1 Dokumen; NF; K; SYC',
                'satker' => 'pjsa',
                'pagu' => 500000000,
            ],
            [
                'kode_paket' => '03.694143.FC.7693.CBG.001.100.A',
                'nama_paket' => 'Supervisi Konstruksi Peningkatan Fungsi Tampungan Bendungan Ladongi untuk mendukung Ketahanan Pangan di Kabupaten Kolaka Timur; Sulawesi Tenggara; Kab. Kolaka Timur; 1 Dokumen; 1 Dokumen ; NF; K; SYC',
                'satker' => 'bendungan',
                'pagu' => 754247000,
            ],
            [
                'kode_paket' => 'BND-101',
                'nama_paket' => 'Supervisi Konstruksi Peningkatan Fungsi Tampungan Bendungan Ladongi untuk mendukung Ketahanan Pangan di Kabupaten Kolaka Timur',
                'satker' => 'bendungan',
                'pagu' => 0,
            ],
        ];

        foreach ($data as $packet) {
            PacketsRegular::create($packet);
        }
    }
}