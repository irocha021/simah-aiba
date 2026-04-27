<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PocoSimahReading extends Model
{
    use SoftDeletes;

    protected $table = 'poco_simah_readings';

    protected $fillable = [
        'poco_simah_station_id',
        'number',
        'datetime_local',
        'datetime_utc',
        'pd_bar',
        'p1_bar',
        'p2_bar',
        'tob1_celsius',
        'tob2_celsius',
    ];

    protected $casts = [
        'datetime_local' => 'datetime',
        'datetime_utc'   => 'datetime',
        'pd_bar'         => 'decimal:15',
        'p1_bar'         => 'decimal:15',
        'p2_bar'         => 'decimal:15',
        'tob1_celsius'   => 'decimal:15',
        'tob2_celsius'   => 'decimal:15',
    ];

    public function station()
    {
        return $this->belongsTo(PocoSimahStation::class);
    }
}
