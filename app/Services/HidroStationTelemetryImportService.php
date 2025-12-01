<?php

namespace App\Services;

use App\Repositories\Interfaces\HwStationTelemetryImportInterface;

class HidroStationTelemetryImportService
{
    protected HwStationTelemetryImportInterface $repository;

    public function __construct(
        HwStationTelemetryImportInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }
}