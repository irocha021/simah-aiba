<?php

namespace App\Services;

use App\Repositories\Interfaces\PocoRimasRepositoryInterface;

class PocoRimasService
{
    protected PocoRimasRepositoryInterface $repository;

    public function __construct(PocoRimasRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function getReadingsByIdPonto(string $idPonto, int $limit = 50)
    {
        return $this->repository->getReadingsByIdPonto($idPonto, $limit);
    }

    public function cursorReadingsByIdPontoAndDateRange(string $idPonto, ?string $dateFrom, ?string $dateTo): \Generator
    {
        return $this->repository->cursorByIdPontoAndDateRange($idPonto, $dateFrom, $dateTo);
    }

    public function getReadingsByIdPontoAndDateRange(string $idPonto, ?string $dateFrom, ?string $dateTo)
    {
        return $this->repository->getReadingsByIdPontoAndDateRange((int) $idPonto, $dateFrom, $dateTo);
    }

}
