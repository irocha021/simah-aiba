<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dcp_sync_logs', function (Blueprint $table) {
            $table->id();

            // Foreign key to dcp_stations
            $table->foreignId('dcp_station_id')->constrained('dcp_stations')->onDelete('cascade');

            // Time period being processed
            $table->dateTime('start_time');
            $table->dateTime('end_time');

            // Processing status
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');

            // Statistics
            $table->integer('total_messages')->default(0);
            $table->integer('total_inserted')->default(0);
            $table->integer('total_corrupted')->default(0);

            // Corrupted message headers for debugging
            $table->json('corrupted_headers')->nullable();

            // Retry tracking
            $table->integer('attempts')->default(0);

            // Error information
            $table->text('error_message')->nullable();

            // Execution timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['dcp_station_id', 'created_at']);
            $table->index('status');
            $table->index('started_at');
            $table->index(['start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dcp_sync_logs');
    }
};