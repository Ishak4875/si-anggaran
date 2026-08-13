<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ditjen_progres', function (Blueprint $table) {
            $table->id();
            $table->decimal('keu', 8, 2)->nullable(); // Progres Keuangan Ditjen SDA (%), input manual
            $table->decimal('fis', 8, 2)->nullable(); // Realisasi Fisik Ditjen SDA (%), input manual
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ditjen_progres');
    }
};
