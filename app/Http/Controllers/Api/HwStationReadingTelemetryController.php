<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HidroStationReadingTelemetryService;
use App\Http\Resources\HwStationReadingTelemetryResource;
use Illuminate\Http\JsonResponse;

class HwStationReadingTelemetryController extends Controller
{
    protected $service;

    public function __construct(HidroStationReadingTelemetryService $service)
    {
        $this->service = $service;
    }

    public function getReadings(string $stationCode): JsonResponse
    {
        $readings = $this->service->getReadingsByStationCode($stationCode, 50);

        return (new HwStationReadingTelemetryResource($readings))
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