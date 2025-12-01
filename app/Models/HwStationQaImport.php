<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HwStationQaImport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hw_station_qa_import';

    protected $fillable = [
        'station_code',
    ];
}