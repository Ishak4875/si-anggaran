<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pekerjaan_rumah_kepala_balais', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pekerjaan');
            $table->string('penanggung_jawab')->nullable();
            $table->date('deadline')->nullable();
            $table->enum('status', ['belum', 'proses', 'selesai'])->default('belum');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('deadline');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pekerjaan_rumah_kepala_balais');
    }
};
