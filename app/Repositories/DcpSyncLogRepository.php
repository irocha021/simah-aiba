<?php
// app/Repositories/DcpSyncLogRepository.php

namespace App\Repositories;

use App\Models\DcpSyncLog;
use App\Repositories\Interfaces\DcpSyncLogRepositoryInterface;
use Illuminate\Support\Collection;

class DcpSyncLogRepository implements DcpSyncLogRepositoryInterface
{
    public function find(int $id): ?DcpSyncLog
    {
        return DcpSyncLog::find($id);
    }
    
    public function create(array $data): DcpSyncLog
    {
        return DcpSyncLog::create($data);
    }
    
    public function update(int $id, array $data): bool
    {
        return DcpSyncLog::where('id', $id)->update($data);
    }
    
    public function getByStation(int $stationId): Collection
    {
        return DcpSyncLog::where('station_id', $stationId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function getLastSyncByStation(int $stationId): ?DcpSyncLog
    {
        return DcpSyncLog::where('station_id', $stationId)
            ->where('status', 'completed')
            ->orderBy('sync_completed_at', 'desc')
            ->first();
    }
    
    public function getRunningSync(int $stationId): ?DcpSyncLog
    {
        return DcpSyncLog::where('station_id', $stationId)
            ->where('status', 'running')
            ->first();
    }
    
    public function getRecentLogs(int $limit = 10): Collection
    {
        return DcpSyncLog::with('station')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    public function getFailedLogs(): Collection
    {
        return DcpSyncLog::where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}