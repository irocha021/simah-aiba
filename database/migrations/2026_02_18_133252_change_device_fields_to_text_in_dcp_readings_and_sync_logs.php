<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dcp_readings', function (Blueprint $table) {
            $table->text('serial_number')->nullable()->change();
            $table->text('program_signature')->nullable()->change();
            $table->text('firmware_version')->nullable()->change();
            $table->text('goes_antenna_signal')->nullable()->change();
            $table->text('program_version')->nullable()->change();
            $table->text('restart_time')->nullable()->change();
            $table->text('sensor_type')->nullable()->change();
            $table->text('transmitter_serial_number')->nullable()->change();
        });

        Schema::table('dcp_sync_logs', function (Blueprint $table) {
            $table->mediumText('error_message')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('dcp_readings', function (Blueprint $table) {
            $table->string('serial_number', 50)->nullable()->change();
            $table->string('program_signature', 100)->nullable()->change();
            $table->string('firmware_version', 50)->nullable()->change();
            $table->string('goes_antenna_signal', 50)->nullable()->change();
            $table->string('program_version', 100)->nullable()->change();
            $table->string('restart_time', 50)->nullable()->change();
            $table->string('sensor_type', 100)->nullable()->change();
            $table->string('transmitter_serial_number', 50)->nullable()->change();
        });

        Schema::table('dcp_sync_logs', function (Blueprint $table) {
            $table->text('error_message')->nullable()->change();
        });
    }
};
