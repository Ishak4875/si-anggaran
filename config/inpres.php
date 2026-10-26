<?php

/*
|--------------------------------------------------------------------------
| Paket Inpres (Daftar Paket Inpres)
|--------------------------------------------------------------------------
| API SIHKA tidak punya penanda "Inpres", jadi paket Inpres dikenali dari
| awalan kdpaket-nya. Dikelompokkan per slug satker (lihat config/satker.php);
| urutan array = urutan tampil. Tambahkan awalan di sini bila ada RO Inpres baru.
*/

return [

    'prefixes' => [

        // Rehabilitasi jaringan tersier D.I. kewenangan daerah/pusat + fasilitasi pemantauannya
        'op' => [
            '03.694136.FC.7695.CDR.008.',
        ],

        // Rehabilitasi jaringan utama, jaringan irigasi air tanah (JIAT), supervisi & pemantauan
        'pjpa' => [
            '03.694244.FC.7691.RBS.005.',
            '03.694244.FC.7694.RBS.006.',
        ],

    ],

];
