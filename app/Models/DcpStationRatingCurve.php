<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DcpStationRatingCurve extends Model
{
    protected $fillable = [
        'dcp_station_id',
        'curva_chave',
        'a',
        'b',
        'c',
        'h0',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'curva_chave' => 'integer',
        'a'          => 'decimal:15',
        'b'          => 'decimal:15',
        'c'          => 'decimal:15',
        'h0'         => 'decimal:15',
        'starts_at'  => 'date',
        'ends_at'    => 'date',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(DcpStation::class, 'dcp_station_id');
    }

    /** Curva sem data fim (ativa/em aberto) */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('ends_at');
    }

    /** Curva vigente em uma data específica */
    public function scopeActiveAt(Builder $query, Carbon $date): Builder
    {
        return $query
            ->where('starts_at', '<=', $date)
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $date));
    }
}
