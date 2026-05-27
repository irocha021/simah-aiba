<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // RPPN INEMA: migrar do raster único (borda preta engrossa no zoom
        // afastado) para translucent_fill por-zoom — mesmo caminho da Bacia
        // do São Francisco e dos Imóveis Rurais. Marrom claro translúcido +
        // borda marrom escuro fina, baseado na referência visual aprovada.
        DB::table('map_layers')
            ->where('slug', 'rppn_inema_2023')
            ->update([
                'translucent_fill' => true,
                'draw_borders'     => true,
                'border_color'     => '180,0,140',                                  // marrom escuro fino
                'border_buffer'    => 0,                                            // piso 0.5px do código
                'palette_text' => "0 0 0 0 0\n1 255 0 200 180\nnv 0 0 0 0", // marrom claro alpha 150
                'opacity'          => 1.0,                                          // translucidez vem do PNG
            ]);
    }

    public function down(): void
    {
        // Reverte só os flags principais (não restaura valores antigos exatos).
        DB::table('map_layers')
            ->where('slug', 'rppn_inema_2023')
            ->update([
                'translucent_fill' => false,
                'opacity'          => 0.60,
            ]);
    }
};
