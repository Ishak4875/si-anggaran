<?php

/*
|--------------------------------------------------------------------------
| Pemetaan Satker BWS Sulawesi IV
|--------------------------------------------------------------------------
| Setiap grup satker memiliki:
|  - nama   : nama lengkap satker
|  - singkatan : label pendek
|  - kdsatker : daftar kode satker (satu grup bisa >1 kode, mis. OP)
|  - warna  : warna segmen pada grafik revisi pagu
|
| Urutan array = urutan tampil di tabel & sidebar.
*/

return [

    'groups' => [

        'balai' => [
            'nama'      => 'Satker BWS Sulawesi IV',
            'singkatan' => 'Balai',
            'kdsatker'  => ['694170'],
            'warna'     => '#2f7d33',
        ],

        'op' => [
            'nama'      => 'Satker Operasi dan Pemeliharaan Sumber Daya Air Sulawesi IV',
            'singkatan' => 'OP',
            'kdsatker'  => ['694136'],
            'warna'     => '#1c6b8c',
        ],

        'pjpa' => [
            'nama'      => 'SNVT PJPA Sulawesi IV Provinsi Sulawesi Tenggara',
            'singkatan' => 'PJPA',
            'kdsatker'  => ['694244'],
            'warna'     => '#a02b8e',
        ],

        'pjsa' => [
            'nama'      => 'SNVT PJSA Sulawesi IV Provinsi Sulawesi Tenggara',
            'singkatan' => 'PJSA',
            'kdsatker'  => ['694243'],
            'warna'     => '#23a6db',
        ],

        'bendungan' => [
            'nama'      => 'SNVT Pembangunan Bendungan Sulawesi IV',
            'singkatan' => 'Bendungan',
            'kdsatker'  => ['694143'],
            'warna'     => '#e2762b',
        ],

    ],

    /*
    | Urutan penumpukan (stack) satker pada grafik revisi pagu, dari bawah ke
    | atas — disesuaikan dengan tampilan yang diinginkan.
    */
    'chart_order' => ['op', 'bendungan', 'balai', 'pjsa', 'pjpa'],

];
