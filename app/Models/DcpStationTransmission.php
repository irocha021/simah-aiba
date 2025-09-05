<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DcpStationTransmission extends Model
{
    use HasFactory;

    protected $table = 'dcp_station_transmissions';

    protected $fillable = [
        'station_id',
        'date',
        'transmit_start',
        'transmit_end',
        'window_start',
        'window_end',
        'failure_code',
        'is_successful',
        'signal_strength',
        'message_length',
        'frequency_offset',
        'modulation_index',
        'drgs_code',
        'battery_voltage',
        'message_filename',
        'message_link'
    ];

    protected $casts = [
        'date' => 'date',
        'window_start' => 'datetime:H:i:s',
        'window_end' => 'datetime:H:i:s',
        'is_successful' => 'boolean',
        'signal_strength' => 'integer',
        'message_length' => 'integer'
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(DcpStation::class, 'station_id');
    }

    public function failureCode(): BelongsTo
    {
        return $this->belongsTo(DcpFailureCode::class, 'failure_code', 'code');
    }

    public function rawData(): HasOne
    {
        return $this->hasOne(DcpTransmissionRawData::class, 'transmission_id');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('is_successful', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('is_successful', false);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }
}