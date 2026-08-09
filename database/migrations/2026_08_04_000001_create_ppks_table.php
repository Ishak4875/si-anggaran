<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('urutan')->default(0); // urutan tampil
            $table->string('jabatan');                     // PPK (Jabatan)
            $table->string('nama')->nullable();            // Nama PPK Saat Ini
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppks');
    }
};
