<?php

namespace App\Repositories\Interfaces;

interface HwEntityInterface
{
  public function store($data);
  public function getAll();
  public function getEntitiesCode();
 
}