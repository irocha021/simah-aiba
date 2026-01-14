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
        Schema::table('hw_inventory_stations', function (Blueprint $table) {
            $table->dropForeign(['file_id_shapefile']);
            $table->dropForeign(['file_id_geojson']);
            $table->dropColumn(['file_id_shapefile', 'file_id_geojson', 'reference_flow']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hw_inventory_stations', function (Blueprint $table) {
            $table->unsignedBigInteger('file_id_shapefile')->nullable();
            $table->unsignedBigInteger('file_id_geojson')->nullable();
            $table->decimal('reference_flow', 25, 20)->default(0);

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
};
