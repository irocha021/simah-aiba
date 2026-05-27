<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hw_station_drainage_layers', function (Blueprint $t) {
            $t->id();
            $t->integer('station_code')->unique();
            $t->string('zip_path')->nullable();
            $t->string('url_pattern')->nullable();
            $t->json('bounds_json')->nullable();
            $t->json('bounds_latlng_json')->nullable();
            $t->enum('status', ['pending', 'processing', 'ready', 'failed'])->default('pending');
            $t->text('status_message')->nullable();
            $t->unsignedTinyInteger('min_zoom')->default(5);
            $t->unsignedTinyInteger('max_zoom')->default(11);
            $t->timestamp('tiles_generated_at')->nullable();
            $t->timestamps();

            $t->index('status');
            $t->foreign('station_code')
                ->references('station_code')->on('hw_inventory_stations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hw_station_drainage_layers');
    }
};