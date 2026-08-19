<?php

namespace Database\Seeders;

use App\Models\PacketsRegular;
use Illuminate\Database\Seeder;

class PacketsRegularSeeder extends Seeder
{
    public function run(): void
    {
        PacketsRegular::truncate();

        $data = array (
  0 =>
  array (
    'kode_paket' => '03.694170.FC.7691.CBR.001.301.A',
    'nama_paket' => 'Desain Rehabilitasi Jaringan Irigasi DI. Mowila Kab. Konawe Selatan; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'balai',
    'pagu' => 1602796000,
  ),
  1 =>
  array (
    'kode_paket' => '03.694170.FC.7691.CBR.001.301.B',
    'nama_paket' => 'Pemutakhiran Pemetaan Daerah Irigasi Kewenangan Pusat di Provinsi Sulawesi Tenggara; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'balai',
    'pagu' => 1764411000,
  ),
  2 =>
  array (
    'kode_paket' => '03.694170.FC.7691.CBR.001.301.C',
    'nama_paket' => 'Penyusunan Dokumen Kesiapan Peningkatan Jaringan Irigasi DI. Watumokala Kab. Konawe Selatan; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'balai',
    'pagu' => 1597768000,
  ),
  3 =>
  array (
    'kode_paket' => '03.694170.FC.7692.CBR.001.301.A',
    'nama_paket' => 'Penyusunan Dokumen Lingkungan Kegiatan Pembangunan Pengaman Pantai Kawasan Anaiwoi - Kampung Bajo Kab. Kolaka; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'balai',
    'pagu' => 846600000,
  ),
  4 =>
  array (
    'kode_paket' => '03.694170.FC.7694.CBR.001.301.A',
    'nama_paket' => 'Desain Rehabilitasi Air Baku Tolihe Kab. Konawe Selatan; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'balai',
    'pagu' => 970140000,
  ),
  5 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.001.103.A',
    'nama_paket' => 'Pengendalian Banjir Sungai Lasusua (Lanjutan) Kab. Kolaka Utara; 0.4000 Km; 4 Ha; F; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 12968144000,
  ),
  6 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.001.100.A',
    'nama_paket' => 'Supervisi Konstruksi Pengendalian Banjir Sungai Lasusua (Lanjutan) Kab. Kolaka Utara; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 630461000,
  ),
  7 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.001.103.B',
    'nama_paket' => 'Pengendalian Banjir Sungai Lasolo (Lanjutan) Kab. Konawe Utara; 0.1000 Km; 1 Ha; F; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 5475692000,
  ),
  8 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.001.100.B',
    'nama_paket' => 'Supervisi Konstruksi Pengendalian Banjir Sungai Lasolo (Lanjutan) Kab. Konawe Utara; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 318194000,
  ),
  9 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.003.103.A',
    'nama_paket' => 'Pembangunan Pengaman Pantai Lasusua (Lanjutan) Kab. Kolaka Utara; 0.5 Km; 5 Ha; F; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 16063925000,
  ),
  10 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.003.100.A',
    'nama_paket' => 'Supervisi Konstruksi Pembangunan Pengaman Pantai Lasusua (Lanjutan) Kab. Kolaka Utara; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 764791000,
  ),
  11 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.003.103.B',
    'nama_paket' => 'Pembangunan Pengaman Pantai Kawasan Anaiwoi-Kampung Bajo Kab. Kolaka; 0.35 Km; 3.5 Ha; F; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 9874189000,
  ),
  12 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.003.100.B',
    'nama_paket' => 'Supervisi Konstruksi Pembangunan Pengaman Pantai Kawasan Anaiwoi-Kampung Bajo Kab. Kolaka; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 518400000,
  ),
  13 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.003.103.C',
    'nama_paket' => 'Pembangunan Pengaman Pantai Tondowolio (Lanjutan) Kab. Kolaka ; 0.58 Km; 5.8 Ha; F; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 17280000000,
  ),
  14 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.003.100.C',
    'nama_paket' => 'Supervisi Konstruksi Pembangunan Pengaman Pantai Tondowolio (Lanjutan) Kab. Kolaka; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 797865000,
  ),
  15 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.003.103.D',
    'nama_paket' => 'Pembangunan Pengaman Pantai Raha (Lanjutan-Tahap III) Kab. Muna; 0.475 Km; 4.75 Ha; F; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 16413438000,
  ),
  16 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.003.100.D',
    'nama_paket' => 'Supervisi Konstruksi Pembangunan Pengaman Pantai Raha (Lanjutan-Tahap III) Kab. Muna; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 798300000,
  ),
  17 =>
  array (
    'kode_paket' => '03.694243.FC.7692.RBS.005.103.A',
    'nama_paket' => 'Pengendalian Banjir Sungai Konaweha Kab. Konawe; 0.3 Km; 3 Ha; F; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 11605988000,
  ),
  18 =>
  array (
    'kode_paket' => '03.694243.FC.7692.RBS.005.100.A',
    'nama_paket' => 'Supervisi Konstruksi Pengendalian Banjir Sungai Konaweha Kab. Konawe; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 599210000,
  ),
  19 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.001.100.C',
    'nama_paket' => 'Supervisi Konstruksi Pembangunan Tanggul Banjir Sungai Wanggu Kota Kendari (Lanjutan); 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 500000000,
  ),
  20 =>
  array (
    'kode_paket' => '03.694243.FC.7692.CBS.001.103.C',
    'nama_paket' => 'Pembangunan Tanggul Banjir Sungai Wanggu Kota Kendari (Lanjutan); 0.8000 Km; 8 Ha; F; K; SYC',
    'satker' => 'pjsa',
    'pagu' => 14500000000,
  ),
  21 =>
  array (
    'kode_paket' => '03.694143.FC.7693.CBG.001.105.A',
    'nama_paket' => 'Peningkatan Fungsi Tampungan Bendungan Ladongi untuk mendukung Ketahanan Pangan di Kabupaten Kolaka Timur; 2 Unit; 0.02 Juta M3; F; K; SYC',
    'satker' => 'bendungan',
    'pagu' => 15544299000,
  ),
  22 =>
  array (
    'kode_paket' => '03.694143.FC.7693.CBG.001.100.A',
    'nama_paket' => 'Supervisi Konstruksi Peningkatan Fungsi Tampungan Bendungan Ladongi untuk mendukung Ketahanan Pangan di Kabupaten Kolaka Timur; Sulawesi Tenggara; Kab. Kolaka Timur; 1 Dokumen; 1 Dokumen ; NF; K; SYC',
    'satker' => 'bendungan',
    'pagu' => 754247000,
  ),
  23 =>
  array (
    'kode_paket' => '03.694244.FC.7691.CBS.002.103.A',
    'nama_paket' => 'Pembangunan Jaringan Irigasi DI Laiba Kab. Muna; 2.07 Km; 156.52 Ha; F; K; SYC',
    'satker' => 'pjpa',
    'pagu' => 19800000000,
  ),
  24 =>
  array (
    'kode_paket' => '03.694244.FC.7691.CBS.002.100.A',
    'nama_paket' => 'Supervisi Konstruksi Pembangunan Jaringan Irigasi DI Laiba Kab. Muna; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'pjpa',
    'pagu' => 1198686000,
  ),
  25 =>
  array (
    'kode_paket' => '03.694244.FC.7691.CBS.002.105.A',
    'nama_paket' => 'Rehabilitasi Jaringan Irigasi D.I Walay Kab. Konawe (Tahap III); 8.995 Km; 1420 Ha; F; K; SYC',
    'satker' => 'pjpa',
    'pagu' => 42000000000,
  ),
  26 =>
  array (
    'kode_paket' => '03.694244.FC.7691.CBS.002.100.B',
    'nama_paket' => 'Supervisi Konstruksi Rehabilitasi Jaringan Irigasi D.I Walay Kab. Konawe (Tahap III); 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'pjpa',
    'pagu' => 2497644000,
  ),
  27 =>
  array (
    'kode_paket' => '03.694244.FC.7694.CBS.001.105.A',
    'nama_paket' => 'Rehabilitasi Jaringan Transmisi Air Baku Kota Baubau; 4 Km; 0.05 M3/Detik; F; K; SYC',
    'satker' => 'pjpa',
    'pagu' => 12773653000,
  ),
  28 =>
  array (
    'kode_paket' => '03.694244.FC.7694.CBS.001.100.A',
    'nama_paket' => 'Supervisi Konstruksi Rehabilitasi Jaringan Transmisi Air Baku Kota Baubau; 1 Dokumen; 1 Dokumen; NF; K; SYC',
    'satker' => 'pjpa',
    'pagu' => 793862000,
  ),
  29 =>
  array (
    'kode_paket' => '03.694244.FC.7694.CBG.001.105.A',
    'nama_paket' => 'Rehabilitasi Embung Ulu Benua Kab. Konawe; 1 Unit; 0.0006 m3/detik; F; K; SYC',
    'satker' => 'pjpa',
    'pagu' => 2773948000,
  ),
);

        foreach ($data as $packet) {
            PacketsRegular::create($packet);
        }
    }
}
