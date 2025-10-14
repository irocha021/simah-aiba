<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HwInventoryStation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hw_inventory_stations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'station_code',
        'station_name',
        'station_uf',
        'station_uf_name',
        'basin_code',
        'basin_name',
        'altitude',
        'latitude',
        'longitude',
        'is_operational',
        'telemetry_station_type',
        'water_quality_station_type',
        'responsible_code',
        'responsible_acronym',
        'responsible_unit_uf',
        'operator_code',
        'operator_abbreviation',
        'operator_sub_unit_state',
        'reference_flow',
        'file_id_shapefile',
        'file_id_geojson',
        'status',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'altitude' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_operational' => 'boolean',
    ];

    /**
     * Get the responsible entity associated with the station.
     */
    public function responsibleEntity()
    {
        return $this->belongsTo(HwEntity::class, 'responsible_code', 'entity_code');
    }

    public function shapefile()
    {
        return $this->belongsTo(File::class, 'file_id_shapefile', 'id');
    }

    public function geojson()
    {
        return $this->belongsTo(File::class, 'file_id_geojson', 'id');
    }

    public function currentFlow()
    {
        return $this->hasOne(HwStationReading::class, 'station_code', 'station_code')
            ->where('adopted_flow', '>', 0)
            ->whereNotNull('adopted_flow')
            ->orderByDesc('measurement_datetime');
    }
}
