<?php
namespace App\Repositories\Interfaces;

use App\Models\DcpStationTransmission;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DcpStationTransmissionRepositoryInterface
{
    public function find(int $id): ?DcpStationTransmission;
    
    public function create(array $data): DcpStationTransmission;
    
    public function getByStation(int $stationId): Collection;
    
    public function getByDate(string $date): Collection;
    
    public function getByStationAndDateRange(int $stationId, string $startDate, string $endDate): Collection;
    
    public function getFailedTransmissions(): Collection;
    
    public function paginate(int $perPage = 15): LengthAwarePaginator;
}