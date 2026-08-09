<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packets', function (Blueprint $table) {
            $table->id();

            // Identitas
            $table->string('tahun', 4)->nullable();
            $table->string('kdsatker', 10)->nullable()->index();
            $table->string('satker_group', 30)->nullable()->index(); // slug grup (config/satker.php)

            // Kode struktur anggaran
            $table->string('kdprogram', 10)->nullable();
            $table->string('kdgiat', 10)->nullable();
            $table->string('kdoutput', 10)->nullable();
            $table->string('kdsoutput', 10)->nullable();
            $table->string('kdkmpnen', 10)->nullable();
            $table->string('kdskmpnen', 10)->nullable();
            $table->string('kdpaket', 100)->nullable()->index();
            $table->text('nmpaket')->nullable();

            $table->string('vol', 50)->nullable();
            $table->string('sat', 50)->nullable();

            // Nilai keuangan (ringkasan)
            $table->decimal('pagu', 20, 2)->nullable();
            $table->decimal('pagu51', 20, 2)->nullable();
            $table->decimal('pagu52', 20, 2)->nullable();
            $table->decimal('pagu53', 20, 2)->nullable();
            $table->decimal('realisasi', 20, 2)->nullable();
            $table->decimal('real_51', 20, 2)->nullable();
            $table->decimal('real_52', 20, 2)->nullable();
            $table->decimal('real_53', 20, 2)->nullable();

            // Progres
            $table->decimal('progres_keuangan', 12, 6)->nullable();
            $table->decimal('real_fisik', 12, 6)->nullable();

            // Seluruh field mentah dari API (agar tidak ada data yang hilang)
            $table->json('raw')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packets');
    }
};
