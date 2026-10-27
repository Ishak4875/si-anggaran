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
        // Status pelaporan per petugas; nama/jabatan tetap di config/kekeringan.php
        Schema::create('pelaporan_kekeringan', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // mis. "D.1.a" (lihat config/kekeringan.php)
            $table->boolean('sudah_lapor')->default(false);
            $table->boolean('kejadian_baru')->default(false);
            $table->boolean('penanganan')->default(false);
            $table->timestamps();
        });

        // Baris tunggal: tanggal & jam "Status : … WITA" yang ditampilkan di atas tabel
        Schema::create('pelaporan_kekeringan_info', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->nullable(); // diisi apa adanya dalam WITA
            $table->time('jam')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelaporan_kekeringan_info');
        Schema::dropIfExists('pelaporan_kekeringan');
    }
};
