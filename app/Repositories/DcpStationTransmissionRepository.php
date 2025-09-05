<?php
namespace App\Repositories;

use App\Models\DcpStationTransmission;
use App\Repositories\Interfaces\DcpStationTransmissionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DcpStationTransmissionRepository implements DcpStationTransmissionRepositoryInterface
{
    public function find(int $id): ?DcpStationTransmission
    {
        return DcpStationTransmission::find($id);
    }
    
    public function create(array $data): DcpStationTransmission
    {
        return DcpStationTransmission::create($data);
    }
    
    public function getByStation(int $stationId): Collection
    {
        return DcpStationTransmission::where('station_id', $stationId)->get();
    }
    
    public function getByDate(string $date): Collection
    {
        return DcpStationTransmission::whereDate('date', $date)->get();
    }
    
    public function getByStationAndDateRange(int $stationId, string $startDate, string $endDate): Collection
    {
        return DcpStationTransmission::where('station_id', $stationId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
    }
    
    public function getFailedTransmissions(): Collection
    {
        return DcpStationTransmission::where('is_successful', false)->get();
    }
    
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return DcpStationTransmission::paginate($perPage);
    }
}