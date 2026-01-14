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
        Schema::create('hw_inventory_station_data', function (Blueprint $table) {
            $table->id();
            $table->integer('station_code')->unique();
            $table->unsignedBigInteger('file_id_shapefile')->nullable();
            $table->unsignedBigInteger('file_id_geojson')->nullable();
            $table->decimal('reference_flow', 25, 20)->default(0);
            $table->decimal('alfa_pond', 15, 8)->nullable();
            $table->decimal('q_noventa', 15, 5)->nullable();
            $table->decimal('vsup', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('station_code')
                ->references('station_code')
                ->on('hw_inventory_stations')
                ->onDelete('cascade');

            $table->foreign('file_id_shapefile')
                ->references('id')
                ->on('files')
                ->onDelete('cascade');

            $table->foreign('file_id_geojson')
                ->references('id')
                ->on('files')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hw_inventory_station_data');
    }
};
