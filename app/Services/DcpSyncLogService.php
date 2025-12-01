<?php

namespace App\Services;

use App\Repositories\Interfaces\DcpSyncLogRepositoryInterface;
use App\Models\DcpSyncLog;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DcpSyncLogService
{
    protected DcpSyncLogRepositoryInterface $syncLogRepository;

    public function __construct(DcpSyncLogRepositoryInterface $syncLogRepository)
    {
        $this->syncLogRepository = $syncLogRepository;
    }

    /**
     * Create a new sync log in pending status
     */
    public function createLog(int $stationId, Carbon $startTime, Carbon $endTime): DcpSyncLog
    {
        return $this->syncLogRepository->create([
            'dcp_station_id' => $stationId,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'pending',
            'attempts' => 0,
        ]);
    }

    /**
     * Start processing a sync log
     */
    public function startLog(int $logId): bool
    {
        $this->syncLogRepository->incrementAttempts($logId);

        return $this->syncLogRepository->update($logId, [
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Complete a sync log with statistics
     */
    public function completeLog(
        int $logId,
        int $totalMessages,
        int $totalInserted,
        int $totalCorrupted,
        array $corruptedHeaders = []
    ): bool {
        return $this->syncLogRepository->update($logId, [
            'status' => 'completed',
            'completed_at' => now(),
            'total_messages' => $totalMessages,
            'total_inserted' => $totalInserted,
            'total_corrupted' => $totalCorrupted,
            'corrupted_headers' => $corruptedHeaders,
        ]);
    }

    /**
     * Mark a sync log as failed with error message
     */
    public function failLog(int $logId, string $errorMessage): bool
    {
        return $this->syncLogRepository->update($logId, [
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Find all pending or stuck logs that can be retried
     */
    public function findPendingForRetry(): Collection
    {
        return $this->syncLogRepository->findPendingForRetry();
    }

    /**
     * Check if a log can be retried (attempts < 3)
     */
    public function canRetry(int $logId): bool
    {
        return $this->syncLogRepository->canRetry($logId);
    }

    /**
     * Get last successful sync for a station
     */
    public function getLastSuccessfulSync(int $stationId): ?DcpSyncLog
    {
        return $this->syncLogRepository->getLastSyncByStation($stationId);
    }

    /**
     * Check if station has a running sync
     */
    public function hasRunningSyncForStation(int $stationId): bool
    {
        return $this->syncLogRepository->getRunningSync($stationId) !== null;
    }

    /**
     * Get sync history for a station
     */
    public function getStationSyncHistory(int $stationId, int $limit = 10): Collection
    {
        return $this->syncLogRepository->getByStation($stationId)->take($limit);
    }

    /**
     * Get recent sync logs across all stations
     */
    public function getRecentSyncLogs(int $limit = 10): Collection
    {
        return $this->syncLogRepository->getRecentLogs($limit);
    }
}
