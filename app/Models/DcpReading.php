<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DcpReading extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'dcp_station_id',
        // Header data
        'address',
        'year',
        'julian_day',
        'hour',
        'minute',
        'second',
        'failure_code',
        'signal_strength',
        'frequency_offset',
        'modulation_index',
        'data_quality',
        'channel',
        'spacecraft',
        'reception_source',
        'data_length',
        'raw_header',
        // Water level readings
        'water_level_120min',
        'water_level_105min',
        'water_level_90min',
        'water_level_75min',
        'water_level_60min',
        'water_level_45min',
        'water_level_30min',
        'water_level_15min',
        // Rain readings
        'rain_120min',
        'rain_105min',
        'rain_90min',
        'rain_75min',
        'rain_60min',
        'rain_45min',
        'rain_30min',
        'rain_15min',
        // Sensors
        'water_temperature',
        'internal_temperature',
        'battery_voltage',
        'level_adjustment',
        'display_value',
        'atmospheric_pressure',
        'latitude',
        'longitude',
        // Device info
        'door_sensor_open',
        'serial_number',
        'program_signature',
        'skipped_scan',
        'operating_system_version',
        'transmitter_serial_number',
        'firmware_version',
        'goes_antenna_signal',
        'program_version',
        'restart_time',
        'sensor_type',
        'extra',
        'reading_datetime',
        'recovered_at',

    ];

    protected $casts = [
        'year' => 'integer',
        'julian_day' => 'integer',
        'hour' => 'integer',
        'minute' => 'integer',
        'second' => 'integer',
        'data_length' => 'integer',
        'water_level_120min' => 'decimal:0',
        'water_level_105min' => 'decimal:0',
        'water_level_90min' => 'decimal:0',
        'water_level_75min' => 'decimal:0',
        'water_level_60min' => 'decimal:0',
        'water_level_45min' => 'decimal:0',
        'water_level_30min' => 'decimal:0',
        'water_level_15min' => 'decimal:0',
        'rain_120min' => 'decimal:1',
        'rain_105min' => 'decimal:1',
        'rain_90min' => 'decimal:1',
        'rain_75min' => 'decimal:1',
        'rain_60min' => 'decimal:1',
        'rain_45min' => 'decimal:1',
        'rain_30min' => 'decimal:1',
        'rain_15min' => 'decimal:1',
        'water_temperature' => 'decimal:1',
        'internal_temperature' => 'decimal:1',
        'battery_voltage' => 'decimal:1',
        'level_adjustment' => 'decimal:1',
        'atmospheric_pressure' => 'decimal:1',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'door_sensor_open' => 'boolean',
        'skipped_scan' => 'integer',
        'reading_datetime' => 'datetime',
        'recovered_at' => 'datetime',
    ];

    /**
     * Relacionamento com DcpStation
     */
    public function dcpStation(): BelongsTo
    {
        return $this->belongsTo(DcpStation::class);
    }
}
