<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dcp_stations', function (Blueprint $table) {
            $table->id();
            $table->string('dcp_address', 20)->unique();
            $table->string('station_name', 100)->nullable();
            $table->string('station_label', 255)->nullable()->comment('Nome amigável definido pelo usuário');
            $table->integer('channel')->nullable();
            $table->string('transmission_interval', 10)->nullable()->comment('ex: 01:00:00');
            $table->time('first_transmission_time')->nullable()->comment('ex: 00:06:20');
            $table->integer('transmission_window')->nullable()->comment('em segundos');
            $table->integer('baud_rate')->nullable()->default(300);
            $table->string('preamble', 5)->nullable()->default('S');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_successful_transmission_at')->nullable();
            $table->timestamps();
            
            $table->index('dcp_address');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dcp_stations');
    }
};