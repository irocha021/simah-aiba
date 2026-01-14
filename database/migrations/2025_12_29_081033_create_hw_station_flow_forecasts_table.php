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
        Schema::create('hw_station_flow_forecasts', function (Blueprint $table) {
            $table->id();
            $table->integer('station_code');
            $table->integer('forecast_year'); // Ano da previsão
            $table->integer('forecast_month'); // Mês da previsão (5=maio, 6=junho, 7=julho)
            $table->decimal('predicted_flow', 15, 5)->nullable(); // Vazão prevista
            $table->decimal('minimum_flow', 15, 5)->nullable(); // Vazão mínima do mês
            $table->integer('forecast_start_day')->nullable(); // Dia do ano de início
            $table->decimal('alfa_pond', 15, 8); // Parâmetro usado
            $table->decimal('q_noventa', 15, 5); // Parâmetro usado
            $table->decimal('vsup', 15, 2); // Parâmetro usado
            $table->timestamps();
            $table->softDeletes();

            // Foreign key
            $table->foreign('station_code')
                  ->references('station_code')
                  ->on('hw_inventory_stations')
                  ->onDelete('cascade');

            // Índices
            $table->index('station_code');
            //$table->index(['forecast_year', 'forecast_month']);
            //$table->unique(['station_code', 'forecast_year', 'forecast_month'], 'hw_flow_forecast_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hw_station_flow_forecasts');
    }
};
