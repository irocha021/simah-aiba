<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DcpFailureCode extends Model
{
    use HasFactory;

    protected $table = 'dcp_failure_codes';

    protected $fillable = [
        'code',
        'description'
    ];

    public function transmissions(): HasMany
    {
        return $this->hasMany(DcpStationTransmission::class, 'failure_code', 'code');
    }
}