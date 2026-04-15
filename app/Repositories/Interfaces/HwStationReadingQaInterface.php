<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Presenters\IPagination;

interface HwStationReadingQaInterface
{   
    public function getAll();
    public function storeReadingsOfStation(array $data);
    public function getExistingRecords($stationCode, $dateInitial, $dateFinal);
    public function insertSingle(array $data);
    public function updateSingle($stationCode, $dataHoraDado, array $data);
    public function deleteByDate($date);
    public function getLastAdoptedFlowByStationCode($stationCode);
    public function paginate(array $options = [], $sort = "id", $order = 'DESC', int $page = 1, int $perPage = 15): IPagination;
    public function getReadingsByStationCode(string $stationCode, int $limit = 50);
    public function getReadingsByStationCodeAndDateRange(string $stationCode, ?string $dateFrom, ?string $dateTo);
} 