<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dcp_reading_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dcp_reading_id')->constrained('dcp_readings')->cascadeOnDelete();
            $table->foreignId('dcp_station_rating_curve_id')->constrained('dcp_station_rating_curves')->cascadeOnDelete();
            $table->decimal('water_level_used', 10, 0);
            $table->tinyInteger('water_level_interval')->comment('15, 30, 45 ou 60');
            $table->decimal('flow', 20, 6);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dcp_reading_flows');
    }
};
