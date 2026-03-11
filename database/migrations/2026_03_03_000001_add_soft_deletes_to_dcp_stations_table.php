<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dcp_readings', function (Blueprint $table) {
            $table->decimal('flow_15min', 20, 6)->nullable()->after('water_level_15min');
        });
    }

    public function down(): void
    {
        Schema::table('dcp_readings', function (Blueprint $table) {
            $table->dropColumn('flow_15min');
        });
    }
};
