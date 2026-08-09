<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagu_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('urutan')->default(0);   // urutan tampil (kolom grafik)
            $table->date('tanggal')->nullable();             // tanggal revisi
            $table->string('keterangan');                    // nama/keterangan revisi
            $table->json('nilai')->nullable();               // { slug_satker: nilai }
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagu_revisions');
    }
};
