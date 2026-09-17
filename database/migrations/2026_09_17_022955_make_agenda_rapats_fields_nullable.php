<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE agenda_rapats MODIFY tanggal_agenda DATE NULL');
        DB::statement('ALTER TABLE agenda_rapats MODIFY waktu TIME NULL');
        DB::statement('ALTER TABLE agenda_rapats MODIFY ruangan VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE agenda_rapats MODIFY tanggal_agenda DATE NOT NULL');
        DB::statement('ALTER TABLE agenda_rapats MODIFY waktu TIME NOT NULL');
        DB::statement('ALTER TABLE agenda_rapats MODIFY ruangan VARCHAR(255) NOT NULL');
    }
};
