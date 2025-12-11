<?php

namespace App\Services;

use App\Repositories\Interfaces\PocoSiagasRepositoryInterface;

class PocoSiagasService
{
    protected $repository;

    public function __construct(PocoSiagasRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getByIdPonto(string $idPonto)
    {
        return $this->repository->getByIdPonto($idPonto);
    }
}