<?php

namespace App\Repositories\Interfaces;

interface JobStatusInterface
{
  public function getAll();
  public function store(array $data);
  public function update($id, $data);
  public function getByStatus(int $job, int $status);
  public function getByDatetimeReadingAndjob($date, $job);
  
}