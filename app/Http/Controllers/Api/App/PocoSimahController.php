<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\PocoSimahReadingRepositoryInterface;
use App\Repositories\Interfaces\PocoSimahStationRepositoryInterface;
use Illuminate\Http\JsonResponse;

class PocoSimahController extends Controller
{
    public function __construct(
        protected PocoSimahStationRepositoryInterface $stationRepository,
        protected PocoSimahReadingRepositoryInterface $readingRepository,
    ) {}

    public function getReadings(string $station_code): JsonResponse
    {
        $station = $this->stationRepository->findByCode($station_code);

        if (!$station) {
            return response()->json(['error' => 'Estação não encontrada'], 404);
        }

        $readings = $this->readingRepository->getByStation($station->id, 100);

        return response()->json([
            'data' => [
                'station' => [
                    'id'           => $station->id,
                    'name'         => $station->name,
                    'station_code' => $station->station_code,
                    'latitude'     => $station->latitude,
                    'longitude'    => $station->longitude,
                    'depth'        => $station->depth,
                ],
                'readings' => $readings,
                'total'    => count($readings),
            ]
        ]);
    }
}
