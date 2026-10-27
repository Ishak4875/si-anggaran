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
            $table->enum('role', ['user', 'admin', 'kpisda', 'kabalai', 'cctv', 'ppk'])->default('user')->change();
            // Akun role "ppk" ditautkan ke satu PPK; hanya melihat CCTV di paket PPK tersebut
            $table->foreignId('ppk_id')->nullable()->after('role')->constrained('ppks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ppk_id');
        });

        // Akun PPK tidak punya padanan lama; turunkan ke 'user' sebelum enum dipersempit.
        DB::table('users')->where('role', 'ppk')->update(['role' => 'user']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin', 'kpisda', 'kabalai', 'cctv'])->default('user')->change();
        });
    }
};
