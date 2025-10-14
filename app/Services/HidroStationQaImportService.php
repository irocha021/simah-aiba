<?php

namespace App\Services;

use App\Repositories\Interfaces\HwStationQaImportInterface;

class HidroStationQaImportService
{
    protected HwStationQaImportInterface $repository;

    public function __construct(
        HwStationQaImportInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }
}