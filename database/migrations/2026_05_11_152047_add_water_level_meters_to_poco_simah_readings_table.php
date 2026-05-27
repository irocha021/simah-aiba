<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poco_simah_readings', function (Blueprint $table) {
            $table->decimal('water_level_meters', 20, 15)->nullable()->after('p1_bar');
        });

        DB::statement('UPDATE poco_simah_readings SET water_level_meters = p1_bar * 10.2 WHERE p1_bar IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('poco_simah_readings', function (Blueprint $table) {
            $table->dropColumn('water_level_meters');
        });
    }
};
