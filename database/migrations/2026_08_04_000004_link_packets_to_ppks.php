<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Kolom slug satker pada ppks (untuk memfilter PPK per satker).
        Schema::table('ppks', function (Blueprint $table) {
            $table->string('satker_group', 30)->nullable()->after('satker')->index();
        });

        $satkerSlug = [
            'Satker BWS Sulawesi IV'                         => 'balai',
            'SNVT PJPA Sulawesi IV Prov. Sulawesi Tenggara'  => 'pjpa',
            'SNVT PJSA Sulawesi IV Prov. Sulawesi Tenggara'  => 'pjsa',
            'SNVT Pembangunan Bendungan Sulawesi IV'         => 'bendungan',
            'Satker OP SDA Sulawesi IV'                      => 'op',
        ];
        foreach ($satkerSlug as $display => $slug) {
            DB::table('ppks')->where('satker', $display)->update(['satker_group' => $slug]);
        }

        // 2) Relasi packets → ppks.
        Schema::table('packets', function (Blueprint $table) {
            $table->foreignId('ppk_id')->nullable()->after('satker_group')
                  ->constrained('ppks')->nullOnDelete();
        });

        // 3) Backfill packets.ppk_id dari kolom string 'ppk' (hasil Excel) via alias
        //    nama Excel → nama jabatan pada tabel ppks.
        if (Schema::hasColumn('packets', 'ppk')) {
            $alias = [
                'PPK Ketatalaksanaan'   => 'PPK Tatalaksana',
                'PPK OP SDA I'          => 'PPK Operasi dan Pemeliharaan SDA 1',
                'PPK OP SDA II'         => 'PPK Operasi dan Pemeliharaan SDA 2',
                'PPK OP SDA III'        => 'PPK Operasi dan Pemeliharaan SDA 3',
                'PPK Irigasi dan Rawa I'   => 'PPK Irigasi dan Rawa 1',
                'PPK Irigasi dan Rawa II'  => 'PPK Irigasi dan Rawa 2',
                'PPK Irigasi dan Rawa III' => 'PPK Irigasi dan Rawa 3',
                'PPK Atab 1'            => 'PPK Air Tanah dan Air Baku 1',
                'PPK Atab 2'            => 'PPK Air Tanah dan Air Baku 2',
                'PPK Atab 3'            => 'PPK Air Tanah dan Air Baku 3',
                'PPK Sungai Dan Pantai I'  => 'PPK Sungai dan Pantai 1',
                'PPK Sungai Dan Pantai II' => 'PPK Sungai dan Pantai 2',
                'PPK Bendungan I'       => 'PPK Bendungan 1',
                'PPK Bendungan II'      => 'PPK Bendungan 2',
                // sisanya sama persis (Perencanaan dan Program, PSDA, Perencanaan Bendungan)
            ];

            $ppksByJabatan = DB::table('ppks')->pluck('id', 'jabatan')->all();

            $distinct = DB::table('packets')->whereNotNull('ppk')->distinct()->pluck('ppk');
            foreach ($distinct as $excelName) {
                $jabatan = $alias[$excelName] ?? $excelName;
                $id      = $ppksByJabatan[$jabatan] ?? null;
                if ($id !== null) {
                    DB::table('packets')->where('ppk', $excelName)->update(['ppk_id' => $id]);
                }
            }
        }

        // 4) Kolom string lama tidak lagi dipakai.
        Schema::table('packets', function (Blueprint $table) {
            if (Schema::hasColumn('packets', 'ppk')) {
                $table->dropColumn('ppk');
            }
            if (Schema::hasColumn('packets', 'ppk_urutan')) {
                $table->dropColumn('ppk_urutan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('packets', function (Blueprint $table) {
            $table->string('ppk')->nullable();
            $table->unsignedInteger('ppk_urutan')->nullable();
            $table->dropConstrainedForeignId('ppk_id');
        });
        Schema::table('ppks', function (Blueprint $table) {
            $table->dropColumn('satker_group');
        });
    }
};
