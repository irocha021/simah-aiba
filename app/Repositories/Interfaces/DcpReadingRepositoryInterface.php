<?php
namespace App\Repositories\Interfaces;

use App\Models\DcpReading;
use Illuminate\Support\Collection;

interface DcpReadingRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?DcpReading;

    public function findByAddress(string $address, int $limit = 50);

    public function create(array $data): DcpReading;

    public function bulkInsert(array $readings): bool;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function findByStationId(int $stationId): Collection;

    public function findByDateRange(int $stationId, string $startDate, string $endDate): Collection;

    public function softDeleteByStationAndPeriod(int $stationId, $startTime, $endTime): int;

    public function findPreviousReading(string $address, string $readingDatetime): ?DcpReading;

    public function updateNullReadings(int $id, array $data): void;

    public function findByAddressAndDateRange(string $address, string $dateFrom, string $dateTo);

    public function cursorByAddressAndDateRange(string $address, ?string $dateFrom, ?string $dateTo): \Generator;

    
}
