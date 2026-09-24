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
            $table->enum('role', ['user', 'admin', 'kpisda', 'kabalai'])->default('user')->change();
        });

        // Non-admin accounts keep the agenda menu they could already see, now expressed as a role.
        if (Schema::hasColumn('users', 'agenda_group')) {
            DB::table('users')->where('role', 'user')->where('agenda_group', 'kabalai')->update(['role' => 'kabalai']);
            DB::table('users')->where('role', 'user')->where('agenda_group', 'kpisda')->update(['role' => 'kpisda']);

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('agenda_group');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('agenda_group', ['kpisda', 'kabalai'])->default('kpisda')->after('role');
        });

        DB::table('users')->where('role', 'kabalai')->update(['agenda_group' => 'kabalai', 'role' => 'user']);
        DB::table('users')->where('role', 'kpisda')->update(['role' => 'user']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin'])->default('user')->change();
        });
    }
};
