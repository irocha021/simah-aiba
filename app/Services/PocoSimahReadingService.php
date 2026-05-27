<?php

namespace App\Services;

use App\Repositories\Interfaces\PocoSimahReadingRepositoryInterface;
use Illuminate\Support\Collection;

class PocoSimahReadingService
{
    public function __construct(
        protected PocoSimahReadingRepositoryInterface $repository,
    ) {}

    public function getByStation(int $stationId, int $limit = 100): array
    {
        return $this->repository->getByStation($stationId, $limit);
    }

    public function getByStationAndDateRange(int $stationId, string $dateFrom, string $dateTo): Collection
    {
        return $this->repository->getByStationAndDateRange($stationId, $dateFrom, $dateTo);
    }

    public function cursorByStationAndDateRange(int $stationId, ?string $dateFrom, ?string $dateTo): \Generator
    {
        return $this->repository->cursorByStationAndDateRange($stationId, $dateFrom, $dateTo);
    }
}
