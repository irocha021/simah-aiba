<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PocoSimahStationResource;
use App\Http\Resources\Public\PocoSimahReadingResource;
use App\Repositories\Interfaces\PocoSimahStationRepositoryInterface;
use App\Repositories\Interfaces\PocoSimahReadingRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PocoSimahController extends Controller
{
    public function __construct(
        protected PocoSimahStationRepositoryInterface $stationRepository,
        protected PocoSimahReadingRepositoryInterface $readingRepository,
    ) {}

    /**
     * Listar estações Poços AIBA
     *
     * Retorna a lista de estações de poços monitorados pelo AIBA.
     *
     * @group Poços AIBA
     *
     * @response {
     *   "data": [
     *     {"station_code": "1676349", "name": "Poço SIMAH 01", "latitude": "-12.9714", "longitude": "-38.5014", "ativa": true}
     *   ],
     *   "meta": {"total": 1, "retrieved_at": "2026-04-25T10:00:00-03:00"}
     * }
     */
    public function stations(): JsonResponse
    {
        $stations = $this->stationRepository->getAllWithCoordinates();

        return response()->json([
            'data' => PocoSimahStationResource::collection($stations),
            'meta' => [
                'total'        => $stations->count(),
                'retrieved_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Leituras da estação Poço AIBA
     *
     * Retorna as leituras de uma estação pelo código, filtradas por período.
     * Os parâmetros date_from e date_to são obrigatórios.
     *
     * @group Poços AIBA
     *
     * @urlParam station_code string required Código da estação. No-example
     * @queryParam date_from string required Data inicial (formato: Y-m-d). No-example
     * @queryParam date_to string required Data final (formato: Y-m-d). No-example
     *
     * @response {
     *   "data": [
     *     {"number": 1, "datetime_local": "2026-04-07T17:25:29.000000Z", "datetime_utc": "2026-04-07T20:25:29.000000Z", "pd_bar": "0.00075674057", "p1_bar": "0.96156311035", "water_level_meters": "9.807943725570000", "p2_bar": "0.96080017090", "tob1_celsius": "25.15673828125", "tob2_celsius": "24.51025390625"}
     *   ],
     *   "meta": {"station_code": "1676349", "total": 1, "period": {"from": "2026-04-01", "to": "2026-04-30"}, "retrieved_at": "2026-04-25T10:00:00-03:00"}
     * }
     */
    public function readings(Request $request, string $stationCode): JsonResponse
    {
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        if (!$dateFrom || !$dateTo) {
            return response()->json(['error' => 'Os parâmetros date_from e date_to são obrigatórios.'], 422);
        }

        if (!\DateTime::createFromFormat('Y-m-d', $dateFrom)) {
            return response()->json(['error' => 'date_from inválido. Use o formato Y-m-d.'], 422);
        }

        if (!\DateTime::createFromFormat('Y-m-d', $dateTo)) {
            return response()->json(['error' => 'date_to inválido. Use o formato Y-m-d.'], 422);
        }

        if ($dateTo < $dateFrom) {
            return response()->json(['error' => 'date_to deve ser maior ou igual a date_from.'], 422);
        }

        $station = $this->stationRepository->findByCode($stationCode);

        if (!$station) {
            return response()->json(['error' => 'Estação não encontrada.'], 404);
        }

        $readings = $this->readingRepository->getByStationAndDateRange($station->id, $dateFrom, $dateTo);

        return response()->json([
            'data' => PocoSimahReadingResource::collection($readings),
            'meta' => [
                'station_code' => $stationCode,
                'total'        => $readings->count(),
                'period'       => [
                    'from' => $dateFrom,
                    'to'   => $dateTo,
                ],
                'retrieved_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
