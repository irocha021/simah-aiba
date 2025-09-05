<?php
// app/Services/DcpSyncLogService.php

namespace App\Services;

use App\Repositories\Interfaces\DcpSyncLogRepositoryInterface;
use App\Models\DcpSyncLog;
use Illuminate\Support\Collection;

class DcpSyncLogService
{
    protected DcpSyncLogRepositoryInterface $syncLogRepository;

    public function __construct(DcpSyncLogRepositoryInterface $syncLogRepository)
    {
        $this->syncLogRepository = $syncLogRepository;
    }

    public function startSync(int $stationId, ?string $dateRangeStart = null, ?string $dateRangeEnd = null): DcpSyncLog
    {
        // Verifica se já existe sync rodando
        $runningSync = $this->syncLogRepository->getRunningSync($stationId);

        if ($runningSync) {
            throw new \Exception("Sync already running for station {$stationId}");
        }

        return $this->syncLogRepository->create([
            'station_id' => $stationId,
            'sync_started_at' => now(),
            'status' => 'running',
            'date_range_start' => $dateRangeStart,
            'date_range_end' => $dateRangeEnd
        ]);

    
    }

    public function completeSync(int $logId, int $transmissionsSaved, int $rawDataSaved): bool
    {
        $log = $this->syncLogRepository->find($logId);
        if (!$log) {
            return false;
        }

        return $this->syncLogRepository->update($logId, [
            'status' => 'completed',
            'sync_completed_at' => now(),
            'transmissions_saved' => $transmissionsSaved,
            'raw_data_saved' => $rawDataSaved,
            'duration_seconds' => $log->sync_started_at->diffInSeconds(now())
        ]);
    }

    public function failSync(int $logId, string $error): bool
    {
        $log = $this->syncLogRepository->find($logId);
        if (!$log) {
            return false;
        }

        $errors = $log->errors ?? [];
        $errors[] = [
            'message' => $error,
            'timestamp' => now()->toIso8601String()
        ];

        return $this->syncLogRepository->update($logId, [
            'status' => 'failed',
            'sync_completed_at' => now(),
            'duration_seconds' => $log->sync_started_at->diffInSeconds(now()),
            'errors' => $errors
        ]);
    }

    public function updateProgress(int $logId, int $transmissionsFound, int $rawDataFetched): bool
    {
        return $this->syncLogRepository->update($logId, [
            'transmissions_found' => $transmissionsFound,
            'raw_data_fetched' => $rawDataFetched
        ]);
    }

    public function getLastSuccessfulSync(int $stationId): ?DcpSyncLog
    {
        return $this->syncLogRepository->getLastSyncByStation($stationId);
    }

    public function hasRunningSyncForStation(int $stationId): bool
    {
        return $this->syncLogRepository->getRunningSync($stationId) !== null;
    }

    public function getStationSyncHistory(int $stationId, int $limit = 10): Collection
    {
        return $this->syncLogRepository->getByStation($stationId)->take($limit);
    }

    public function getRecentSyncLogs(int $limit = 10): Collection
    {
        return $this->syncLogRepository->getRecentLogs($limit);
    }
}