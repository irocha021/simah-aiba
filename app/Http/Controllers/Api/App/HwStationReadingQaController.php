<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\App\HwStationReadingQaResource;
use App\Services\HidroStationReadingQaService;
use Illuminate\Http\JsonResponse;

class HwStationReadingQaController extends Controller
{
    protected $service;

    public function __construct(HidroStationReadingQaService $service)
    {
        $this->service = $service;
    }

    public function getReadings(string $stationCode): JsonResponse
    {
        $readings = $this->service->getReadingsByStationCode($stationCode, 50);

        return (new HwStationReadingQaResource($readings))
            ->additional([
                'success' => true,
                'meta' => [
                    'station_code' => $stationCode,
                    'total' => $readings->count()
                ]
            ])
            ->response();
    }
}