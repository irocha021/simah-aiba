<?php

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;


interface PocoSimahReadingRepositoryInterface
{
    public function upsertBatch(int $stationId, array $records): int;
    public function getByStation(int $stationId, int $limit = 100): array;
    public function deleteByStation(int $stationId): bool;
    public function getByStationAndDateRange(int $stationId, string $dateFrom, string $dateTo): Collection;
}
