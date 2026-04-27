<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poco_simah_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poco_simah_station_id')
                  ->constrained('poco_simah_stations')
                  ->cascadeOnDelete();
            $table->unsignedInteger('number')->nullable();
            $table->timestamp('datetime_local')->nullable();
            $table->timestamp('datetime_utc');
            $table->decimal('pd_bar', 20, 15)->nullable();
            $table->decimal('p1_bar', 20, 15)->nullable();
            $table->decimal('p2_bar', 20, 15)->nullable();
            $table->decimal('tob1_celsius', 20, 15)->nullable();
            $table->decimal('tob2_celsius', 20, 15)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['poco_simah_station_id', 'datetime_utc'], 'unique_station_datetime');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poco_simah_readings');
    }
};
