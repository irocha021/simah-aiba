<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Inserir camada Bacia do São Francisco ──────────────────
        // Usa o caminho translucent_fill (borda + preenchimento translúcido
        // gerado POR ZOOM — não engorda a borda no zoom afastado, ao
        // contrário do ba_uf_ibge_2019 que usa raster único).
        DB::table('map_layers')->updateOrInsert(
            ['slug' => 'bacia_sao_francisco'],
            [
                'name'             => 'Bacia do São Francisco',
                'type'             => 'tile',
                'source_type'      => 'polygon',
                'is_active'        => true,
                'has_legend'       => false,
                'display_order'    => 16,
                'attribution'      => '',
                'min_zoom'         => 5,
                'max_zoom'         => 12,
                'opacity'          => 1.0,
                'tms'              => true,
                'path_zip'         => 'storage/app/shapefiles/Bacias.zip',
                'url_pattern'      => '/tiles/bacia_sao_francisco/{z}/{x}/{y}.png',
                'marker_color'     => null,
                'marker_radius'    => null,
                'field_name'       => null,
                'single_color'     => true,
                'borders_only'     => false,
                'draw_borders'     => true,
                'translucent_fill' => true,
                'border_color'     => '0,0,0',                                     // borda preta fina
                'border_buffer'    => 0,                                           // piso 0.5px do código
                'sql_mapping_json' => null,
                'palette_text'     => "0 0 0 0 0\n1 111 141 106 255\nnv 0 0 0 0", // verde-sálvia #6F8D6A
                'created_at'       => now(),
                'updated_at'       => now(),
            ]
        );

        
        // ── 2. Fixar config validada dos Imóveis Rurais ───────────────
        // Estes valores foram calibrados manualmente no banco durante os
        // testes; gravar aqui garante que um ambiente novo (ou reset do
        // banco) reproduza o estilo aprovado, e não o do seed original.
        DB::table('map_layers')
            ->where('slug', 'imovel_rural_limite_propriedade_inema')
            ->update([
                'translucent_fill' => true,
                'draw_borders'     => true,
                'border_color'     => '225,225,0',                                 // amarelo forte
                'border_buffer'    => 0,                                           // piso 0.5px do código
                'palette_text'     => "0 0 0 0 0\n1 230 220 60 50\nnv 0 0 0 0",  // amarelo translúcido alpha 50
                'opacity'          => 1.0,
                'min_zoom'         => 5,
                'max_zoom'         => 12,
            ]);
    }

    public function down(): void
    {
        DB::table('map_layers')->where('slug', 'bacia_sao_francisco')->delete();
    }
};
