<?php

namespace Database\Seeders;

use App\Models\Ppk;
use Illuminate\Database\Seeder;

class PpkSeeder extends Seeder
{
    public function run(): void
    {
        $bws       = 'Satker BWS Sulawesi IV';
        $pjpa      = 'SNVT PJPA Sulawesi IV Prov. Sulawesi Tenggara';
        $pjsa      = 'SNVT PJSA Sulawesi IV Prov. Sulawesi Tenggara';
        $bendungan = 'SNVT Pembangunan Bendungan Sulawesi IV';
        $op        = 'Satker OP SDA Sulawesi IV';

        // [jabatan, satker, nama]  (nama null bila belum diketahui)
        $data = [
            // Satker BWS Sulawesi IV
            ['PPK Perencanaan dan Program', $bws, null],
            ['PPK Tatalaksana', $bws, null],
            ['PPK PSDA', $bws, null],

            // Satker OP SDA Sulawesi IV
            ['PPK Operasi dan Pemeliharaan SDA 1', $op, 'Yasser, S.T.'],
            ['PPK Operasi dan Pemeliharaan SDA 2', $op, 'Jose Rizal Luhut Marolop Panjaitan, ST.,MT'],
            ['PPK Operasi dan Pemeliharaan SDA 3', $op, 'Ir. Wahyuddin Qadri S, S.T., M.T.'],

            // SNVT PJPA
            ['PPK Irigasi dan Rawa 1', $pjpa, 'Ir. Wagiyo, S.T., M.Si.'],
            ['PPK Irigasi dan Rawa 2', $pjpa, 'Ir. Iping Mariandana Alwi, S.T., M.Eng.'],
            ['PPK Irigasi dan Rawa 3', $pjpa, 'Rano Karno, S.T.'],
            ['PPK Air Tanah dan Air Baku 1', $pjpa, 'Ir. Novril, S.T., M.T.'],
            ['PPK Air Tanah dan Air Baku 2', $pjpa, 'Ir. Arif Sidik, S.T., M.Eng.'],
            ['PPK Air Tanah dan Air Baku 3', $pjpa, 'Ir. Annur Ramadhani Asana, S.Si., M.T.'],

            // SNVT PJSA
            ['PPK Sungai dan Pantai 1', $pjsa, 'Noto Prayitno, ST'],
            ['PPK Sungai dan Pantai 2', $pjsa, 'Juli Ibrahim, S.T.'],

            // SNVT Pembangunan Bendungan
            ['PPK Perencanaan Bendungan', $bendungan, 'Ir. Rachmat Deby, S.T., M.T'],
            ['PPK Bendungan 1', $bendungan, 'Ir. Arifuddin, S.T.'],
            ['PPK Bendungan 2', $bendungan, 'Muhammad Ryzhal Ariztha, S.T., M.T.'],
        ];

        foreach ($data as $i => [$jabatan, $satker, $nama]) {
            Ppk::updateOrCreate(
                ['jabatan' => $jabatan],
                ['urutan' => $i, 'satker' => $satker, 'nama' => $nama],
            );
        }
    }
}
