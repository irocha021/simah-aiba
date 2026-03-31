<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DcpReadingFlow extends Model
{
    protected $fillable = [
        'dcp_reading_id',
        'dcp_station_rating_curve_id',
        'water_level_used',
        'water_level_interval',
        'flow',
    ];

    protected $casts = [
        'water_level_used'     => 'decimal:0',
        'water_level_interval' => 'integer',
        'flow'                 => 'decimal:6',
    ];

    public function reading(): BelongsTo
    {
        return $this->belongsTo(DcpReading::class, 'dcp_reading_id');
    }

    public function ratingCurve(): BelongsTo
    {
        return $this->belongsTo(DcpStationRatingCurve::class, 'dcp_station_rating_curve_id');
    }
}
