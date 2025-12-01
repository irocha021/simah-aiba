<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hw_station_telemetry_import', function (Blueprint $table) {
            $table->id();
            $table->string('station_code', 20);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('station_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hw_station_telemetry_import');
    }
};