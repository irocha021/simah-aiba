<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HwStationDrainageLayer extends Model
{
    protected $table = 'hw_station_drainage_layers';

    protected $fillable = [
        'station_code',
        'zip_path',
        'url_pattern',
        'bounds_json',
        'bounds_latlng_json',
        'status',
        'status_message',
        'min_zoom',
        'max_zoom',
        'tiles_generated_at',
    ];

    protected $casts = [
        'bounds_json' => 'array',
        'bounds_latlng_json' => 'array',
        'tiles_generated_at' => 'datetime',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(HwInventoryStation::class, 'station_code', 'station_code');
    }
}
