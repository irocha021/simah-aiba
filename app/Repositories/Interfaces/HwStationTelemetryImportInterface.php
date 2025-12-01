<?php

namespace App\Repositories\Interfaces;

interface HwStationTelemetryImportInterface
{
  public function store($data);
  public function getAll();
  #public function getEntitiesCode();
 
}