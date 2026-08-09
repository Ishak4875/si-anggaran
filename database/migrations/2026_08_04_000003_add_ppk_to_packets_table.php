<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packets', function (Blueprint $table) {
            $table->string('ppk')->nullable()->after('satker_group')->index();
            $table->unsignedInteger('ppk_urutan')->nullable()->after('ppk');
        });
    }

    public function down(): void
    {
        Schema::table('packets', function (Blueprint $table) {
            $table->dropColumn(['ppk', 'ppk_urutan']);
        });
    }
};
