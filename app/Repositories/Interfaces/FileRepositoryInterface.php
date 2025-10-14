<?php

namespace App\Repositories\Interfaces;

interface FileRepositoryInterface
{
  public function store(array $data);
  public function get($request);
  public function getAll();
  public function delete($id); // Adicione esta linha
}
