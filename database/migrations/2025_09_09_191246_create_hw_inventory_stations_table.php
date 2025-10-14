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
        Schema::create('hw_inventory_stations', function (Blueprint $table) {
            $table->id();
            $table->integer('station_code')->unique();
            $table->string('station_name', 255)->nullable();
            $table->string('station_uf', 255)->nullable();
            $table->string('station_uf_name', 255)->nullable();
            $table->integer('basin_code')->nullable();
            $table->string('basin_name', 255)->nullable();
            $table->decimal('altitude', 10, 6)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_operational')->default(false);
            $table->boolean('telemetry_station_type')->default(false);
            $table->boolean('water_quality_station_type')->default(false);
            $table->integer('responsible_code');
            $table->string('responsible_acronym', 255)->nullable();
            $table->integer('responsible_unit_uf')->nullable();
            $table->integer('operator_code')->nullable();
            $table->string('operator_abbreviation', 255)->nullable();
            $table->integer('operator_sub_unit_state')->nullable();
            $table->boolean('status')->default(true)->nullable();
            $table->unsignedBigInteger('file_id_shapefile')->nullable();
            $table->unsignedBigInteger('file_id_geojson')->nullable();
            $table->decimal('reference_flow', 25, 20)->default(0);
            
            // Timestamps com softDeletes
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('file_id_shapefile')
                  ->references('id')
                  ->on('files')
                  ->onDelete('set null');
                  
            $table->foreign('file_id_geojson')
                  ->references('id')
                  ->on('files')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('station_code');
            $table->index('is_operational');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hw_inventory_stations');
    }
};