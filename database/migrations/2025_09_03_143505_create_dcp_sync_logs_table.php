<?php
// database/migrations/2024_01_01_000005_create_dcp_sync_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dcp_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('dcp_stations')->onDelete('cascade');
            $table->timestamp('sync_started_at');
            $table->timestamp('sync_completed_at')->nullable();
            $table->integer('transmissions_found')->default(0);
            $table->integer('transmissions_saved')->default(0);
            $table->integer('raw_data_fetched')->default(0);
            $table->integer('raw_data_saved')->default(0);
            $table->json('errors')->nullable();
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'partial'])->default('pending');
            $table->string('date_range_start', 20)->nullable();
            $table->string('date_range_end', 20)->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();
            
            $table->index(['station_id', 'created_at']);
            $table->index('status');
            $table->index('sync_started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dcp_sync_logs');
    }
};