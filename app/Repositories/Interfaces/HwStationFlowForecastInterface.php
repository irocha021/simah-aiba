<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Presenters\IPagination;

interface HwStationFlowForecastInterface
{
    public function store(array $data);
    public function updateOrCreate(array $conditions, array $data);
    public function getByStationAndPeriod(int $stationCode, int $year, int $month);
    public function getAll();
    public function paginate(array $options = [], $sort = "id", $order = 'DESC', int $page = 1, int $perPage = 15): IPagination;
    public function deleteByYearMonth(int $year, int $month);
    public function deleteByStationYearMonth(int $stationCode, int $year, int $month);
}
