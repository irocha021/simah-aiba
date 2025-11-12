<?php

namespace App\Repositories;

use App\Models\DcpReading;
use App\Repositories\Interfaces\DcpReadingRepositoryInterface;
use Illuminate\Support\Collection;

class DcpReadingRepository implements DcpReadingRepositoryInterface
{
    public function all(): Collection
    {
        return DcpReading::all();
    }

    public function find(int $id): ?DcpReading
    {
        return DcpReading::find($id);
    }

    public function findByAddress(string $address): Collection
    {
        return DcpReading::where('address', $address)->get();
    }

    public function create(array $data): DcpReading
    {
        return DcpReading::create($data);
    }

    public function bulkInsert(array $readings): bool
    {
        return DcpReading::insert($readings);
    }

    public function update(int $id, array $data): bool
    {
        return DcpReading::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return DcpReading::destroy($id);
    }

    public function findByStationId(int $stationId): Collection
    {
        return DcpReading::where('dcp_station_id', $stationId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByDateRange(int $stationId, string $startDate, string $endDate): Collection
    {
        return DcpReading::where('dcp_station_id', $stationId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function softDeleteByStationAndPeriod(int $stationId, $startTime, $endTime): int
    {
        return DcpReading::where('dcp_station_id', $stationId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('year', [$startTime->year, $endTime->year])
                    ->whereBetween('julian_day', [$startTime->dayOfYear, $endTime->dayOfYear]);
            })
            ->delete(); // SoftDeletes trait makes this a soft delete
    }
}
