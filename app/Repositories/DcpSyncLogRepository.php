<?php

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
        return DcpSyncLog::where('dcp_station_id', $stationId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLastSyncByStation(int $stationId): ?DcpSyncLog
    {
        return DcpSyncLog::where('dcp_station_id', $stationId)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->first();
    }

    public function getRunningSync(int $stationId): ?DcpSyncLog
    {
        return DcpSyncLog::where('dcp_station_id', $stationId)
            ->where('status', 'running')
            ->first();
    }

    public function getRecentLogs(int $limit = 10): Collection
    {
        return DcpSyncLog::with('dcpStation')
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

    public function findPendingForRetry(): Collection
    {
        // Filtro `created_at < now()-15min` impede que o reprocessador capture
        // logs criados na invocação atual (uma estação que falhou no mesmo run
        // não deve ser re-tentada imediatamente — fica para o próximo cron).
        return DcpSyncLog::where(function ($query) {
            $query->where('status', 'pending')
                ->orWhere('status', 'failed')
                ->orWhere(function ($subQuery) {
                    $subQuery->where('status', 'running')
                        ->where('started_at', '<', now()->subMinutes(10));
                });
        })
            ->where('attempts', '<', 3)
            ->where('created_at', '<', now()->subMinutes(15))
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function canRetry(int $id): bool
    {
        $log = $this->find($id);
        return $log && $log->attempts < 3;
    }

    public function incrementAttempts(int $id): bool
    {
        return DcpSyncLog::where('id', $id)->increment('attempts');
    }
}