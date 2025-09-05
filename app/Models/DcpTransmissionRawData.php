<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DcpTransmissionRawData extends Model
{
    use HasFactory;

    protected $table = 'dcp_transmission_raw_data';

    protected $fillable = [
        'transmission_id',
        'raw_data'
    ];

    public function transmission(): BelongsTo
    {
        return $this->belongsTo(DcpStationTransmission::class, 'transmission_id');
    }
}