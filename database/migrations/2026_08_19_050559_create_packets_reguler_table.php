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
        Schema::create('packets_reguler', function (Blueprint $table) {
            $table->id();
            $table->string('kode_paket')->unique();
            $table->string('nama_paket');
            $table->enum('satker', ['balai', 'op', 'pjpa', 'pjsa', 'bendungan']);
            $table->bigInteger('pagu');
            $table->timestamps();
            $table->index('nama_paket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packets_reguler');
    }
};
