<?php
namespace App\Services;

use App\Repositories\Interfaces\DcpStationTransmissionRepositoryInterface;
use App\Repositories\Interfaces\DcpTransmissionRawDataRepositoryInterface;
use App\Models\DcpStationTransmission;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DcpStationTransmissionService
{
    protected DcpStationTransmissionRepositoryInterface $transmissionRepository;
    protected DcpTransmissionRawDataRepositoryInterface $rawDataRepository;

    public function __construct(
        DcpStationTransmissionRepositoryInterface $transmissionRepository,
        DcpTransmissionRawDataRepositoryInterface $rawDataRepository
    ) {
        $this->transmissionRepository = $transmissionRepository;
        $this->rawDataRepository = $rawDataRepository;
    }

    public function getTransmission(int $id): ?DcpStationTransmission
    {
        return $this->transmissionRepository->find($id);
    }

    public function getStationTransmissions(int $stationId): Collection
    {
        return $this->transmissionRepository->getByStation($stationId);
    }

    public function getTransmissionsByDate(string $date): Collection
    {
        return $this->transmissionRepository->getByDate($date);
    }

    public function getStationTransmissionsByDateRange(int $stationId, string $startDate, string $endDate): Collection
    {
        return $this->transmissionRepository->getByStationAndDateRange($stationId, $startDate, $endDate);
    }

    public function getFailedTransmissions(): Collection
    {
        return $this->transmissionRepository->getFailedTransmissions();
    }

    public function paginateTransmissions(int $perPage = 15): LengthAwarePaginator
    {
        return $this->transmissionRepository->paginate($perPage);
    }

    public function createTransmissionWithRawData(array $transmissionData, ?string $rawData = null): DcpStationTransmission
    {
        return DB::transaction(function () use ($transmissionData, $rawData) {
            $transmission = $this->transmissionRepository->create($transmissionData);
            
            if ($rawData !== null) {
                $this->rawDataRepository->create([
                    'transmission_id' => $transmission->id,
                    'raw_data' => $rawData
                ]);
            }
            
            return $transmission;
        });
    }
}