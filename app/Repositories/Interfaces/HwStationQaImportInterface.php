<?php

namespace App\Repositories\Interfaces;

interface HwStationQaImportInterface
{
  public function store($data);
  public function getAll();
  public function storeByStationCode(int $stationCode): void;
  public function deleteByStationCode(int $stationCode): void;

  #public function getEntitiesCode();
 
}