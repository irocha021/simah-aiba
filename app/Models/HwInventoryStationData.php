<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HwInventoryStationData extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hw_inventory_station_data';

    protected $fillable = [
        'station_code',
        'reference_flow',
        'alfa_pond',
        'q_noventa',
        'vsup',
    ];

    protected $casts = [
        'reference_flow' => 'float',
        'alfa_pond'      => 'float',
        'q_noventa'      => 'float',
        'vsup'           => 'float',
    ];
}
