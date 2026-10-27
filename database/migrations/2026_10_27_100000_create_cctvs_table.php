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
        Schema::create('cctvs', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            // Device ID Tuya; satu kamera fisik hanya terpasang di satu paket pada satu waktu
            $table->string('device_id')->unique();
            // Dikaitkan lewat kdpaket, bukan packets.id: sinkron API menghapus & mengisi ulang tabel packets
            $table->string('kdpaket')->index();
            $table->string('lokasi')->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cctvs');
    }
};
