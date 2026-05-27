<?php

namespace App\Services;

use App\Repositories\Interfaces\HwStationReadingQaInterface;

class HidroStationReadingQaService
{
    protected HwStationReadingQaInterface $repository;

    public function __construct(
        HwStationReadingQaInterface $repository
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

    public function getExistingRecords($stationCode, $dateInitial, $dateFinal)
    {
        return $this->repository->getExistingRecords($stationCode, $dateInitial, $dateFinal);
    }

    public function updateReading($stationCode, $dataHoraDado, array $data)
{
    return $this->repository->updateSingle($stationCode, $dataHoraDado, $data);
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

    public function cursorReadingsByStationCodeAndDateRange(string $stationCode, ?string $dateFrom, ?string $dateTo): \Generator
    {
        return $this->repository->cursorByStationCodeAndDateRange($stationCode, $dateFrom, $dateTo);
    }

    public function paginateReadings(string $stationCode, ?string $dateFrom, ?string $dateTo, int $page, int $perPage)
    {
        return $this->repository->paginateReadings($stationCode, $dateFrom, $dateTo, $page, $perPage);
    }

}