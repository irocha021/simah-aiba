<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dcp_station_transmissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('dcp_stations')->onDelete('cascade');
            $table->date('date');
            $table->string('transmit_start', 20)->nullable()->comment('ex: 16:06:21.2 ou --:--:--');
            $table->string('transmit_end', 20)->nullable()->comment('ex: 16:06:28.8 ou --:--:--');
            $table->time('window_start')->nullable();
            $table->time('window_end')->nullable();
            $table->string('failure_code', 5)->nullable();
            $table->boolean('is_successful')->default(false);
            $table->integer('signal_strength')->nullable()->comment('em dBM, 0 quando falha');
            $table->integer('message_length')->nullable()->comment('em bytes');
            $table->string('frequency_offset', 10)->nullable();
            $table->string('modulation_index', 5)->nullable();
            $table->string('drgs_code', 10)->nullable()->comment('ex: N2, UP, UB');
            $table->string('battery_voltage', 20)->nullable();
            $table->string('message_filename', 100)->nullable();
            $table->string('message_link', 255)->nullable();
            $table->timestamps();
            
            $table->index(['station_id', 'date'], 'idx_station_date');
            $table->index('date', 'idx_date');
            $table->index('message_filename', 'idx_filename');
            $table->index('failure_code', 'idx_failure');
            
            $table->foreign('failure_code')->references('code')->on('dcp_failure_codes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dcp_station_transmissions');
    }
};