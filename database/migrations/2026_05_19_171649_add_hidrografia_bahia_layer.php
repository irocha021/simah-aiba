<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Adicionar coluna line_style (flag do caminho de LINHA) ──
        if (!Schema::hasColumn('map_layers', 'line_style')) {
            Schema::table('map_layers', function (Blueprint $table) {
                $table->boolean('line_style')->default(false);
            });
        }

        // ── 2. Inserir camada Hidrografia da Bahia ────────────────────
        // Geometria de LINHA (rios). Usa o caminho line_style (rasteriza
        // o traço direto, por zoom — não engorda no zoom afastado). O
        // nome do rio (campo NOME) vira label via extractLabels existente.
        DB::table('map_layers')->updateOrInsert(
            ['slug' => 'hidrografia_bahia'],
            [
                'name'             => 'Hidrografia da Bahia',
                'type'             => 'tile',
                'source_type'      => 'polygon', // enum não tem 'line'; inerte (service decide por flag)
                'is_active'        => true,
                'has_legend'       => false,
                'display_order'    => 17,
                'attribution'      => '',
                'min_zoom'         => 5,
                'max_zoom'         => 12,
                'opacity'          => 1.0,
                'tms'              => true,
                'path_zip'         => 'storage/app/shapefiles/Bahia.zip',
                'url_pattern'      => '/tiles/hidrografia_bahia/{z}/{x}/{y}.png',
                'marker_color'     => null,
                'marker_radius'    => null,
                'field_name'       => null,
                'single_color'     => false,
                'borders_only'     => false,
                'draw_borders'     => false,
                'translucent_fill' => false,
                'line_style'       => true,
                'border_color'     => '0,100,200',  // azul fino (reusa coluna p/ cor da linha)
                'border_buffer'    => 0,
                'label_field'      => 'NOME',        // nome do rio
                'sql_mapping_json' => null,
                'palette_text'     => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('map_layers')->where('slug', 'hidrografia_bahia')->delete();

        if (Schema::hasColumn('map_layers', 'line_style')) {
            Schema::table('map_layers', function (Blueprint $table) {
                $table->dropColumn('line_style');
            });
        }
    }
};
