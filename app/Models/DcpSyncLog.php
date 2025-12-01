<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DcpSyncLog extends Model
{
    use HasFactory;

    protected $table = 'dcp_sync_logs';

    protected $fillable = [
        'dcp_station_id',
        'start_time',
        'end_time',
        'status',
        'total_messages',
        'total_inserted',
        'total_corrupted',
        'corrupted_headers',
        'attempts',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_messages' => 'integer',
        'total_inserted' => 'integer',
        'total_corrupted' => 'integer',
        'corrupted_headers' => 'array',
        'attempts' => 'integer',
    ];

    public function dcpStation(): BelongsTo
    {
        return $this->belongsTo(DcpStation::class, 'dcp_station_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeStuck($query)
    {
        // Running jobs that started more than 10 minutes ago
        return $query->where('status', 'running')
            ->where('started_at', '<', now()->subMinutes(10));
    }
}