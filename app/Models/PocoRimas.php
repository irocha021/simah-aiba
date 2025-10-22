<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PocoRimas extends Model
{
    use SoftDeletes;

    protected $table = 'pocos_rimas';

    protected $fillable = [
        'id_ponto',
        'latiold',
        'longold',
        'numero_de',
        'data_da_me',
        'hora_da_me',
        'nivel_da_a',
        'field_8',
        'latitude_d',
        'longitude',
    ];

    protected $casts = [
        'id_ponto' => 'integer',
        'numero_de' => 'integer',
        'nivel_da_a' => 'decimal:15',
        'latitude_d' => 'decimal:15',
        'longitude' => 'decimal:15',
    ];
}
