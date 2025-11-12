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
        Schema::create('dcp_readings', function (Blueprint $table) {
            $table->id();

            // Foreign key
            $table->foreignId('dcp_station_id')->constrained('dcp_stations')->onDelete('cascade');

            // Raw header (column 0 from partsOfMessage)
            $table->text('raw_header')->nullable();

            // Header data from DcpHeaderDto
            $table->string('address', 20);
            $table->integer('year');
            $table->integer('julian_day');
            $table->integer('hour');
            $table->integer('minute');
            $table->integer('second');
            $table->string('failure_code', 5)->nullable();
            $table->string('signal_strength', 10)->nullable();
            $table->string('frequency_offset', 10)->nullable();
            $table->string('modulation_index', 5)->nullable();
            $table->string('data_quality', 5)->nullable();
            $table->string('channel', 10)->nullable();
            $table->string('spacecraft', 5)->nullable();
            $table->string('reception_source', 10)->nullable();
            $table->integer('data_length')->nullable();

            // Water level readings (8 readings, 120min to 15min ago) - sem casas decimais
            $table->decimal('water_level_120min', 10, 0)->nullable();
            $table->decimal('water_level_105min', 10, 0)->nullable();
            $table->decimal('water_level_90min', 10, 0)->nullable();
            $table->decimal('water_level_75min', 10, 0)->nullable();
            $table->decimal('water_level_60min', 10, 0)->nullable();
            $table->decimal('water_level_45min', 10, 0)->nullable();
            $table->decimal('water_level_30min', 10, 0)->nullable();
            $table->decimal('water_level_15min', 10, 0)->nullable();

            // Rain readings (8 readings, 120min to 15min ago) - 1 casa decimal
            $table->decimal('rain_120min', 10, 1)->nullable();
            $table->decimal('rain_105min', 10, 1)->nullable();
            $table->decimal('rain_90min', 10, 1)->nullable();
            $table->decimal('rain_75min', 10, 1)->nullable();
            $table->decimal('rain_60min', 10, 1)->nullable();
            $table->decimal('rain_45min', 10, 1)->nullable();
            $table->decimal('rain_30min', 10, 1)->nullable();
            $table->decimal('rain_15min', 10, 1)->nullable();

            // Temperature and other sensor readings - 1 casa decimal
            $table->decimal('water_temperature', 8, 1)->nullable();
            $table->decimal('internal_temperature', 8, 1)->nullable();
            $table->decimal('battery_voltage', 8, 1)->nullable();

            // Station measurements - 1 casa decimal
            $table->decimal('level_adjustment', 10, 1)->nullable();
            $table->string('display_value', 50)->nullable();
            $table->decimal('atmospheric_pressure', 10, 1)->nullable();

            // Location
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();

            // Device information
            $table->boolean('door_sensor_open')->nullable();
            $table->string('serial_number', 50)->nullable();
            $table->string('program_signature', 100)->nullable();
            $table->integer('skipped_scan')->nullable();
            $table->string('operating_system_version', 50)->nullable();
            $table->string('transmitter_serial_number', 50)->nullable();
            $table->string('firmware_version', 50)->nullable();
            $table->string('goes_antenna_signal', 50)->nullable();
            $table->string('program_version', 100)->nullable();
            $table->string('restart_time', 50)->nullable();
            $table->string('sensor_type', 100)->nullable();
            $table->string('extra', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('dcp_station_id');
            $table->index('address');
            $table->index(['year', 'julian_day']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dcp_readings');
    }
};
