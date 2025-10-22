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
        Schema::create('pocos_siagas', function (Blueprint $table) {
            $table->id();

            // Identificação
            $table->bigInteger('ponto')->nullable();
            $table->string('localizaca', 254)->nullable();
            $table->string('data_insta', 254)->nullable();

            // Coordenadas
            $table->decimal('cota_terre', 24, 15)->nullable();
            $table->decimal('latitude_d', 24, 15)->nullable();
            $table->decimal('longitude_', 24, 15)->nullable();
            $table->bigInteger('utme')->nullable();
            $table->bigInteger('utmn')->nullable();

            // Localização geográfica
            $table->string('bacia', 254)->nullable();
            $table->string('municipio', 254)->nullable();
            $table->string('natureza', 254)->nullable();
            $table->string('nome', 254)->nullable();
            $table->string('proprietar', 254)->nullable();
            $table->string('subbacia', 254)->nullable();
            $table->string('situacao', 254)->nullable();
            $table->string('uf', 254)->nullable();
            $table->string('uso_agua', 254)->nullable();

            // Informações de perfuração
            $table->string('data_perfu', 254)->nullable();
            $table->string('metodo_per', 254)->nullable();
            $table->string('perfurador', 254)->nullable();
            $table->decimal('diametro_b', 24, 15)->nullable();
            $table->decimal('topo', 24, 15)->nullable();
            $table->decimal('base', 24, 15)->nullable();
            $table->string('tipo_penet', 254)->nullable();
            $table->string('condicao', 254)->nullable();
            $table->string('tipo_capta', 254)->nullable();

            // Medições
            $table->string('data_medic', 254)->nullable();
            $table->decimal('nivel_agua', 24, 15)->nullable();
            $table->decimal('vazao', 24, 15)->nullable();
            $table->string('nivel_bomb', 254)->nullable();
            $table->boolean('profundida')->nullable();
            $table->decimal('profundi_1', 24, 15)->nullable();
            $table->string('tipo_forma', 254)->nullable();

            // Testes
            $table->string('data_teste', 254)->nullable();
            $table->string('tipo_teste', 254)->nullable();
            $table->string('metodo_int', 254)->nullable();
            $table->string('surgencia', 254)->nullable();
            $table->string('unidade_de', 254)->nullable();
            $table->decimal('nivel_dina', 24, 15)->nullable();
            $table->decimal('nivel_esta', 24, 15)->nullable();
            $table->decimal('vazao_espe', 24, 15)->nullable();
            $table->string('coeficient', 254)->nullable();
            $table->string('vazao_livr', 254)->nullable();
            $table->string('permeabili', 254)->nullable();
            $table->string('transmissi', 254)->nullable();
            $table->decimal('vazao_esta', 24, 15)->nullable();
            $table->string('tipo_bomba', 254)->nullable();

            // Análise de qualidade da água
            $table->string('data_anali', 254)->nullable();
            $table->string('data_colet', 254)->nullable();
            $table->decimal('condutivid', 24, 15)->nullable();
            $table->decimal('cor', 24, 15)->nullable();
            $table->string('odor', 254)->nullable();
            $table->string('sabor', 254)->nullable();
            $table->decimal('temperatur', 24, 15)->nullable();
            $table->decimal('turbidez', 24, 15)->nullable();
            $table->string('solidos_se', 254)->nullable();
            $table->decimal('solidos_su', 24, 15)->nullable();
            $table->string('aspecto_na', 254)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pocos_siagas');
    }
};
