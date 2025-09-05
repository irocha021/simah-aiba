<?php
// app/Models/DcpSyncLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DcpSyncLog extends Model
{
    use HasFactory;

    protected $table = 'dcp_sync_logs';

    protected $fillable = [
        'station_id',
        'sync_started_at',
        'sync_completed_at',
        'transmissions_found',
        'transmissions_saved',
        'raw_data_fetched',
        'raw_data_saved',
        'errors',
        'status',
        'date_range_start',
        'date_range_end',
        'duration_seconds'
    ];

    protected $casts = [
        'sync_started_at' => 'datetime',
        'sync_completed_at' => 'datetime',
        'transmissions_found' => 'integer',
        'transmissions_saved' => 'integer',
        'raw_data_fetched' => 'integer',
        'raw_data_saved' => 'integer',
        'errors' => 'array',
        'duration_seconds' => 'integer'
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(DcpStation::class, 'station_id');
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

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'sync_completed_at' => now(),
            'duration_seconds' => now()->diffInSeconds($this->sync_started_at)
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $errors = $this->errors ?? [];
        $errors[] = [
            'message' => $error,
            'timestamp' => now()->toIso8601String()
        ];

        $this->update([
            'status' => 'failed',
            'sync_completed_at' => now(),
            'duration_seconds' => now()->diffInSeconds($this->sync_started_at),
            'errors' => $errors
        ]);
    }

    public function addError(string $error): void
    {
        $errors = $this->errors ?? [];
        $errors[] = [
            'message' => $error,
            'timestamp' => now()->toIso8601String()
        ];

        $this->update(['errors' => $errors]);
    }
}