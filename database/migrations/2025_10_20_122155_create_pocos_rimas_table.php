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
        Schema::create('pocos_rimas', function (Blueprint $table) {
            $table->id();

            // Identificação
            $table->bigInteger('id_ponto')->nullable();
            $table->string('latiold', 254)->nullable();
            $table->string('longold', 254)->nullable();

            // Número de medição
            $table->bigInteger('numero_de')->nullable();

            // Data e hora
            $table->string('data_da_me', 254)->nullable();
            $table->string('hora_da_me', 12)->nullable();

            // Medição
            $table->decimal('nivel_da_a', 24, 15)->nullable();
            $table->string('field_8', 254)->nullable();

            // Coordenadas
            $table->decimal('latitude_d', 24, 15)->nullable();
            $table->decimal('longitude', 24, 15)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pocos_rimas');
    }
};
