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
        Schema::create('agenda_kepala_balais', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_agenda')->nullable();
            $table->string('nama_agenda');
            $table->time('waktu')->nullable();
            $table->string('ruangan')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('tanggal_agenda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_kepala_balais');
    }
};
