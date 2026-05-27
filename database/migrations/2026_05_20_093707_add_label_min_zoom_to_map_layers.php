<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona coluna label_min_zoom com default 8.
        // - Todas as camadas existentes herdam 8 (mesmo valor que estava
        //   hardcoded no JS antes — preserva comportamento de Municípios SEI).
        // - Por-camada: cada slug pode sobrescrever esse valor.
        if (!Schema::hasColumn('map_layers', 'label_min_zoom')) {
            Schema::table('map_layers', function (Blueprint $table) {
                $table->integer('label_min_zoom')->default(8);
            });
        }

        // Hidrografia da Bahia: nomes dos rios só a partir do zoom 10.
        // Em zoom <10 a malha cobre a Bahia inteira e os ~1500 nomes poluem
        // a tela; a partir de 10 fica legível.
        DB::table('map_layers')
            ->where('slug', 'hidrografia_bahia')
            ->update(['label_min_zoom' => 10]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('map_layers', 'label_min_zoom')) {
            Schema::table('map_layers', function (Blueprint $table) {
                $table->dropColumn('label_min_zoom');
            });
        }
    }
};
