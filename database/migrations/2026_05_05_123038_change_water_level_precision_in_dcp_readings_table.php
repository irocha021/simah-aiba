<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'water_level_120min',
        'water_level_105min',
        'water_level_90min',
        'water_level_75min',
        'water_level_60min',
        'water_level_45min',
        'water_level_30min',
        'water_level_15min',
    ];

    public function up(): void
    {
        Schema::table('dcp_readings', function (Blueprint $table) {
            foreach ($this->columns as $col) {
                $table->decimal($col, 8, 2)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('dcp_readings', function (Blueprint $table) {
            foreach ($this->columns as $col) {
                $table->decimal($col, 10, 0)->nullable()->change();
            }
        });
    }
};
