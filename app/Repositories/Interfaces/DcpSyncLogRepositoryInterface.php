<?php

namespace App\Repositories\Interfaces;

use App\Models\DcpSyncLog;
use Illuminate\Support\Collection;

interface DcpSyncLogRepositoryInterface
{
    public function find(int $id): ?DcpSyncLog;

    public function create(array $data): DcpSyncLog;

    public function update(int $id, array $data): bool;

    public function getByStation(int $stationId): Collection;

    public function getLastSyncByStation(int $stationId): ?DcpSyncLog;

    public function getRunningSync(int $stationId): ?DcpSyncLog;

    public function getRecentLogs(int $limit = 10): Collection;

    public function getFailedLogs(): Collection;

    /**
     * Find all pending or stuck logs that can be retried (attempts < 3)
     */
    public function findPendingOrStuck(): Collection;

    /**
     * Check if a log can be retried (attempts < 3)
     */
    public function canRetry(int $id): bool;

    /**
     * Increment the attempts counter for a log
     */
    public function incrementAttempts(int $id): bool;
}