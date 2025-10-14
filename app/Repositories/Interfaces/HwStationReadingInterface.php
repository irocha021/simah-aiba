<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Presenters\IPagination;

interface HwStationReadingInterface
{   
    public function getAll();
    public function storeReadingsOfStation(array $data);
    public function deleteByDate($date);
    public function getLastAdoptedFlowByStationCode($stationCode);
    public function paginate(array $options = [], $sort = "id", $order = 'DESC', int $page = 1, int $perPage = 15): IPagination;
}