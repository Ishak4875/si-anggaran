<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menentukan menu agenda mana yang boleh dilihat user non-admin.
            $table->enum('agenda_group', ['kpisda', 'kabalai'])->default('kpisda')->after('role');
        });

        DB::table('users')->where('email', 'kabalai@gmail.com')->update(['agenda_group' => 'kabalai']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('agenda_group');
        });
    }
};
