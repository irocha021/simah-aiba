<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PocoSimahStation extends Model
{
    use SoftDeletes;

    protected $table = 'poco_simah_stations';

    protected $fillable = [
        'name',
        'station_code',
        'latitude',
        'longitude',
        'ativa',
    ];

    protected $casts = [
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
        'ativa'     => 'boolean',
    ];

    public function readings()
    {
        return $this->hasMany(PocoSimahReading::class);
    }
}
