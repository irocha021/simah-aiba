<?php

namespace App\Repositories\Interfaces;

use App\Repositories\Presenters\IPagination;

interface HwInventoryStationInterface
{   
    public function getAll($onlyDisplayInSystem = false);
    public function getAllWithArea();
    public function getOperationalStations();
    public function getByStationCode(int $stationCode);
    public function getStatusActive();
    public function getStationsByType(string $type = null, bool $active=true);
    public function update(int $id, array $data);
    public function updateFilesId(int $stationCode, array $data);
    public function updateReferenceFlow(int $stationCode, array $data);
    public function deleteFiles(int $stationCode, array $data);
    public function changeStatus(int $stationCode, array $data);
    public function store(array $data);
    public function paginate(array $options = [], $sort = "id", $order = 'DESC', int $page = 1, int $perPage = 15): IPagination;
    public function getAllWithCoordinates();
}