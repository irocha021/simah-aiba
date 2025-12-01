<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LrgsServer extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'lrgs_servers';

    protected $fillable = [
        'address',
        'main'
    ];
}