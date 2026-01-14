<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HwStationFlowForecast extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hw_station_flow_forecasts';

    protected $fillable = [
        'station_code',
        'forecast_year',
        'forecast_month',
        'predicted_flow',
        'minimum_flow',
        'forecast_start_day',
        'alfa_pond',
        'q_noventa',
        'vsup',
    ];

    protected $casts = [
        'predicted_flow' => 'float',
        'minimum_flow' => 'float',
        'forecast_start_day' => 'integer',
        'alfa_pond' => 'float',
        'q_noventa' => 'float',
        'vsup' => 'float',
    ];

    public function station()
    {
        return $this->belongsTo(HwInventoryStation::class, 'station_code', 'station_code');
    }
}
