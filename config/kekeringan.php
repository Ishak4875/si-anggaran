<?php

/*
|--------------------------------------------------------------------------
| Monitoring Pelaporan Kekeringan — daftar petugas (statis)
|--------------------------------------------------------------------------
| Nama, jabatan, dan pembagian TRC tidak diubah lewat aplikasi; yang disimpan
| di database hanya status pelaporan per petugas (dikunci oleh "kode", jadi
| jangan ubah kode yang sudah dipakai — status lamanya akan hilang).
*/

return [

    'grup' => [
        [
            'kode' => 'D.1',
            'nama' => 'TRC 1 (Kendari, Konut, Konawe, Konkep)',
            'anggota' => [
                ['kode' => 'D.1.a', 'nama' => 'Ir. Rheinhardt Maupa, ST., MT', 'jabatan' => 'Wakil Kordinator Wilayah Kendari'],
                ['kode' => 'D.1.b', 'nama' => 'Nurdin, ST', 'jabatan' => 'Wakil Kordinator Wilayah Konawe'],
                ['kode' => 'D.1.c', 'nama' => 'Hali Hamanto, SE., MM', 'jabatan' => 'Wakil Kordinator Wilayah Konawe Kepulauan'],
                ['kode' => 'D.1.d', 'nama' => 'Muhammad Ryzhal Ariztha, ST., MT', 'jabatan' => 'Wakil Kordinator Wilayah Konawe Utara'],
            ],
        ],
        [
            'kode' => 'D.2',
            'nama' => 'TRC 2 (Koltim, Kolaka, Kolut, Konsel, Bombana)',
            'anggota' => [
                ['kode' => 'D.2.a', 'nama' => 'Ir. Arifuddin, ST', 'jabatan' => 'Wakil Kordinator Wilayah Bombana'],
                ['kode' => 'D.2.b', 'nama' => 'Annur Ramadhani Asana, S.Si., MT', 'jabatan' => 'Wakil Kordinator Wilayah Kolaka'],
                ['kode' => 'D.2.c', 'nama' => 'Ir. Novril, ST., MT', 'jabatan' => 'Wakil Kordinator Wilayah Kolaka Utara'],
                ['kode' => 'D.2.d', 'nama' => 'Rano Karno, ST', 'jabatan' => 'Wakil Kordinator Wilayah Kolaka Timur'],
                ['kode' => 'D.2.e', 'nama' => 'Ir. Rachmat Deby, ST., MT', 'jabatan' => 'Wakil Kordinator Wilayah Konawe Selatan'],
            ],
        ],
        [
            'kode' => 'D.3',
            'nama' => 'TRC 3 (Baubau, Buton, Butur, Busel, Buteng, Muna, Mubar, Wakatobi)',
            'anggota' => [
                ['kode' => 'D.3.a', 'nama' => 'Ir. Iping Mariandana Alwi, ST., M.Eng', 'jabatan' => 'Wakil Kordinator Wilayah Buton dan Busel'],
                ['kode' => 'D.3.b', 'nama' => 'Ir.Wagiyo, ST., M.Si', 'jabatan' => 'Wakil Kordinator Wilayah Muna dan Mubar'],
                ['kode' => 'D.3.c', 'nama' => 'Ir. Arif Sidik, ST., M.Eng', 'jabatan' => 'Wakil Kordinator Wilayah Wakatobi dan Butur'],
                ['kode' => 'D.3.d', 'nama' => 'Juli Ibrahim, ST', 'jabatan' => 'Wakil Kordinator Wilayah Baubau dan Buteng'],
            ],
        ],
    ],

];
