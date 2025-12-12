<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Lrgs\DcpReadingService;
use App\Http\Resources\DcpReadingResource;
use Illuminate\Http\JsonResponse;

class LrgsClientController extends Controller
{
    protected $service;

    public function __construct(DcpReadingService $service)
    {
        $this->service = $service;
    }

    public function getReadings(string $stationCode): JsonResponse
    {
        $readings = $this->service->getReadingsByAddress($stationCode, 50);

        return (new DcpReadingResource($readings))
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