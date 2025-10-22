<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PocoSiagas extends Model
{
    use SoftDeletes;

    protected $table = 'pocos_siagas';

    protected $fillable = [
        'ponto',
        'localizaca',
        'data_insta',
        'cota_terre',
        'latitude_d',
        'longitude_',
        'utme',
        'utmn',
        'bacia',
        'municipio',
        'natureza',
        'nome',
        'proprietar',
        'subbacia',
        'situacao',
        'uf',
        'uso_agua',
        'data_perfu',
        'metodo_per',
        'perfurador',
        'diametro_b',
        'topo',
        'base',
        'tipo_penet',
        'condicao',
        'tipo_capta',
        'data_medic',
        'nivel_agua',
        'vazao',
        'nivel_bomb',
        'profundida',
        'profundi_1',
        'tipo_forma',
        'data_teste',
        'tipo_teste',
        'metodo_int',
        'surgencia',
        'unidade_de',
        'nivel_dina',
        'nivel_esta',
        'vazao_espe',
        'coeficient',
        'vazao_livr',
        'permeabili',
        'transmissi',
        'vazao_esta',
        'tipo_bomba',
        'data_anali',
        'data_colet',
        'condutivid',
        'cor',
        'odor',
        'sabor',
        'temperatur',
        'turbidez',
        'solidos_se',
        'solidos_su',
        'aspecto_na',
    ];

    protected $casts = [
        'ponto' => 'integer',
        'cota_terre' => 'decimal:15',
        'latitude_d' => 'decimal:15',
        'longitude_' => 'decimal:15',
        'utme' => 'integer',
        'utmn' => 'integer',
        'diametro_b' => 'decimal:15',
        'topo' => 'decimal:15',
        'base' => 'decimal:15',
        'nivel_agua' => 'decimal:15',
        'vazao' => 'decimal:15',
        'profundida' => 'boolean',
        'profundi_1' => 'decimal:15',
        'nivel_dina' => 'decimal:15',
        'nivel_esta' => 'decimal:15',
        'vazao_espe' => 'decimal:15',
        'vazao_esta' => 'decimal:15',
        'condutivid' => 'decimal:15',
        'cor' => 'decimal:15',
        'temperatur' => 'decimal:15',
        'turbidez' => 'decimal:15',
        'solidos_su' => 'decimal:15',
    ];
}
