<?php

namespace App\Repositories\Interfaces;

interface HwStationQaImportInterface
{
  public function store($data);
  public function getAll();
  #public function getEntitiesCode();
 
}