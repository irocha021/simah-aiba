<?php
// app/Services/DcpStationService.php

namespace App\Services;

use App\Repositories\Interfaces\DcpStationRepositoryInterface;
use App\Models\DcpStation;
use Illuminate\Support\Collection;

class DcpStationService
{
    protected DcpStationRepositoryInterface $stationRepository;

    public function __construct(DcpStationRepositoryInterface $stationRepository)
    {
        $this->stationRepository = $stationRepository;
    }

    public function getAllStations(): Collection
    {
        return $this->stationRepository->all();
    }

    public function getActiveStations(): Collection
    {
        return $this->stationRepository->getActive();
    }

    public function getStation(int $id): ?DcpStation
    {
        return $this->stationRepository->find($id);
    }

    public function getStationByDcpAddress(string $dcpAddress): ?DcpStation
    {
        return $this->stationRepository->findByDcpAddress($dcpAddress);
    }

    public function createStation(array $data): DcpStation
    {
        return $this->stationRepository->create($data);
    }

    public function updateStation(int $id, array $data): bool
    {
        return $this->stationRepository->update($id, $data);
    }

    public function deleteStation(int $id): bool
    {
        return $this->stationRepository->delete($id);
    }

    public function toggleStationStatus(int $id): bool
    {
        $station = $this->stationRepository->find($id);
        if (!$station) {
            return false;
        }
        
        return $this->stationRepository->update($id, [
            'is_active' => !$station->is_active
        ]);
    }
}