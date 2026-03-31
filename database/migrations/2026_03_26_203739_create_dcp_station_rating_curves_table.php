<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dcp_station_rating_curves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dcp_station_id')->constrained('dcp_stations')->cascadeOnDelete();
            $table->tinyInteger('curva_chave'); // 1 = 1ª Curva, 2 = 2ª Curva
            $table->decimal('a', 20, 15);
            $table->decimal('b', 20, 15);
            $table->decimal('c', 20, 15)->nullable(); // só para 2ª curva
            $table->decimal('h0', 20, 15)->nullable(); // só para 1ª curva
            $table->date('starts_at');
            $table->date('ends_at')->nullable(); // null = período em aberto (ativo)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dcp_station_rating_curves');
    }
};
