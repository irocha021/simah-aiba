<?php

namespace App\Services;

use App\Repositories\Interfaces\HwStationFlowForecastInterface;

class HidroStationFlowForecastService
{
    protected HwStationFlowForecastInterface $repository;

    public function __construct(HwStationFlowForecastInterface $repository)
    {
        $this->repository = $repository;
    }

    public function store(array $data)
    {
        return $this->repository->store($data);
    }

    public function updateOrCreate(array $conditions, array $data)
    {
        return $this->repository->updateOrCreate($conditions, $data);
    }

    public function getByStationAndPeriod(int $stationCode, int $year, int $month)
    {
        return $this->repository->getByStationAndPeriod($stationCode, $year, $month);
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function paginate(array $options = [], $sort = "id", $order = 'DESC', int $page = 1, int $perPage = 15)
    {
        return $this->repository->paginate($options, $sort, $order, $page, $perPage);
    }

    public function deleteByYearMonth(int $year, int $month)
    {
        return $this->repository->deleteByYearMonth($year, $month);
    }

    public function deleteByStationYearMonth(int $stationCode, int $year, int $month)
    {
        return $this->repository->deleteByStationYearMonth($stationCode, $year, $month);
    }
}
