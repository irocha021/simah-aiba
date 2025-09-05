<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dcp_transmission_raw_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transmission_id')->unique()->constrained('dcp_station_transmissions')->onDelete('cascade');
            $table->text('raw_data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dcp_transmission_raw_data');
    }
};