<?php

namespace App\Services;

use App\Repositories\Interfaces\PocoRimasRepositoryInterface;

class PocoRimasService
{
    protected $repository;

    public function __construct(PocoRimasRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function getReadingsByIdPonto(string $idPonto, int $limit = 50)
    {
        return $this->repository->getReadingsByIdPonto($idPonto, $limit);
    }

    
    
}
