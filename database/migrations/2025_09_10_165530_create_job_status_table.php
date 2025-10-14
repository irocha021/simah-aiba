<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('job');
            $table->date('datetime_reading');
            $table->unsignedTinyInteger('status');
            $table->json('logs')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('job');
            $table->index('status');
            $table->index('datetime_reading');
            $table->index(['job', 'datetime_reading']);
        });

        // Adicionar constraints usando raw SQL para MySQL
        // Check constraint para job (1 a 20)
        DB::statement('ALTER TABLE job_status ADD CONSTRAINT job_status_job_check CHECK (job >= 1 AND job <= 20)');
        
        // Check constraint para status (1 a 20)
        DB::statement('ALTER TABLE job_status ADD CONSTRAINT job_status_status_check CHECK (status >= 1 AND status <= 20)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover constraints antes de dropar a tabela
        DB::statement('ALTER TABLE job_status DROP CONSTRAINT IF EXISTS job_status_job_check');
        DB::statement('ALTER TABLE job_status DROP CONSTRAINT IF EXISTS job_status_status_check');
        
        Schema::dropIfExists('job_status');
    }
};