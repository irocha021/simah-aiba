<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HwStationTelemetryImport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hw_station_telemetry_import';

    protected $fillable = [
        'station_code',
    ];
}