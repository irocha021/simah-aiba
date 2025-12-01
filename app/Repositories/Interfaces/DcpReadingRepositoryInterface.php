<?php
namespace App\Repositories\Interfaces;

use App\Models\DcpReading;
use Illuminate\Support\Collection;

interface DcpReadingRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?DcpReading;

    public function findByAddress(string $address): Collection;

    public function create(array $data): DcpReading;

    public function bulkInsert(array $readings): bool;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function findByStationId(int $stationId): Collection;

    public function findByDateRange(int $stationId, string $startDate, string $endDate): Collection;

    public function softDeleteByStationAndPeriod(int $stationId, $startTime, $endTime): int;
}
