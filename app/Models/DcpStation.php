<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DcpStation extends Model
{
    use HasFactory;

    protected $table = 'dcp_stations';

    protected $fillable = [
        'dcp_address',
        'station_name',
        'station_label',
        'channel',
        'transmission_interval',
        'first_transmission_time',
        'transmission_window',
        'baud_rate',
        'preamble',
        'is_active',
        'last_successful_transmission_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'channel' => 'integer',
        'transmission_window' => 'integer',
        'baud_rate' => 'integer',
        'last_successful_transmission_at' => 'datetime',
        'first_transmission_time' => 'datetime:H:i:s'
    ];

    public function transmissions(): HasMany
    {
        return $this->hasMany(DcpStationTransmission::class, 'station_id');
    }

    public function successfulTransmissions(): HasMany
    {
        return $this->transmissions()->where('is_successful', true);
    }

    public function failedTransmissions(): HasMany
    {
        return $this->transmissions()->where('is_successful', false);
    }
}