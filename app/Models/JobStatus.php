<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobStatus extends Model
{
    use HasFactory;

    protected $table = 'job_status';

    protected $fillable = [
        'job',
        'datetime_reading',
        'status',
        'logs'
    ];

    protected $dates = [
        'datetime_reading',
        'created_at',
        'updated_at'
    ];
}
