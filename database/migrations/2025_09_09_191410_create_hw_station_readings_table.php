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
        Schema::create('hw_station_readings', function (Blueprint $table) {
            $table->id();
            $table->integer('station_code');
            $table->decimal('adopted_rainfall', 10, 2)->nullable();
            $table->decimal('adopted_quota', 10, 2)->nullable();
            $table->decimal('adopted_flow', 10, 2)->nullable();
            $table->timestamp('measurement_datetime')->nullable();
            
            // Timestamps com softDeletes
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key
            $table->foreign('station_code')
                  ->references('station_code')
                  ->on('hw_inventory_stations')
                  ->onDelete('cascade');
            
            // Indexes
            $table->index('station_code');
            $table->index('measurement_datetime');
            $table->index(['station_code', 'measurement_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hw_station_readings');
    }
};