<?php

namespace App\Services;

use App\Repositories\Interfaces\HwStationReadingTelemetryInterface;

class HidroStationReadingTelemetryService
{
    protected HwStationReadingTelemetryInterface $repository;

    public function __construct(
        HwStationReadingTelemetryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function storeReadingsOfStation(array $data)
    {   
        return $this->repository->storeReadingsOfStation($data);
    }

    public function deleteByDate($date) 
    {
        return $this->repository->deleteByDate($date);
    }

    public function getLastAdoptedFlowByStationCode($stationCode)
    {
        return $this->repository->getLastAdoptedFlowByStationCode($stationCode);
    }
    
    public function paginate(array $options = [], $sort = "id", $order = 'DESC', int $page = 1, int $perPage = 15)
    {
        return $this->repository->paginate($options, $sort, $order, $page, $perPage);
    }

    public function getReadingsByStationCode(string $stationCode, int $limit = 50)
{
    return $this->repository->getReadingsByStationCode($stationCode, $limit);
}

}