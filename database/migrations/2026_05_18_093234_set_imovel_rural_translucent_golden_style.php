<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('map_layers', 'translucent_fill')) {
            Schema::table('map_layers', function (Blueprint $table) {
                $table->boolean('translucent_fill')->default(false);
            });
        }

        DB::table('map_layers')
            ->where('slug', 'imovel_rural_limite_propriedade_inema')
            ->update([
                'translucent_fill' => true,
                'draw_borders'     => true,
                'border_color'     => '200,170,40',                              // dourado
                'border_buffer'    => 1,                                          // px no zoom alto (interpolado 0.3..1 por zoom)
                'palette_text'     => "0 0 0 0 0\n1 250 248 210 90\nnv 0 0 0 0", // fill pálido, alpha 90
                'opacity'          => 1.0,                                        // translucidez já vem no PNG
            ]);
    }

    public function down(): void
    {
        DB::table('map_layers')
            ->where('slug', 'imovel_rural_limite_propriedade_inema')
            ->update([
                'translucent_fill' => false,
                'draw_borders'     => false,
                'border_color'     => null,
                'border_buffer'    => null,
                'palette_text'     => "0 0 0 0 0\n1 255 235 100 255\nnv 0 0 0 0",
                'opacity'          => 0.90,
            ]);

        if (Schema::hasColumn('map_layers', 'translucent_fill')) {
            Schema::table('map_layers', function (Blueprint $table) {
                $table->dropColumn('translucent_fill');
            });
        }
    }
};
