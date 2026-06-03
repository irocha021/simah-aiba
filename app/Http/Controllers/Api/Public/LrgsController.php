<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\App\LrgsReadingResource;
use App\Http\Resources\App\LrgsStationResource;
use App\Repositories\Interfaces\DcpStationRepositoryInterface;
use App\Services\Lrgs\DcpReadingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LrgsController extends Controller
{
    public function __construct(
        protected DcpStationRepositoryInterface $stationRepository,
        protected DcpReadingService $readingService
    ) {}

    /**
     * Listar estações
     *
     * Retorna a lista de todas as estações LRGS cadastradas.
     *
     * @group Estações AIBA
     *
     * @response {
     *   "data": [
     *     {"dcp_address": "B04041E0", "station_label": "Estação Principal - Rio São Francisco", "latitude": -12.1437, "longitude": -45.0107, "is_active": true},
     *     {"dcp_address": "CE342D34", "station_label": "Teste", "latitude": null, "longitude": null, "is_active": true}
     *   ],
     *   "meta": {"total": 2, "retrieved_at": "2026-04-13T14:01:21-03:00"}
     * }
     */
    public function stations(): JsonResponse
    {
        $stations = $this->stationRepository->all();

        return response()->json([
            'data' => LrgsStationResource::collection($stations),
            'meta' => [
                'total'        => $stations->count(),
                'retrieved_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Leituras da estação
     *
     * Retorna as leituras de uma estação LRGS pelo endereço DCP.
     * Se não informar as datas, retorna as últimas 72 horas.
     *
     * @group Estações AIBA
     *
     * @urlParam station_code string required Endereço DCP da estação. Example: B04041E0
     * @queryParam date_from string required Data inicial (formato: Y-m-d). No-example
     * @queryParam date_to string required Data final (formato: Y-m-d). No-example
     * @bodyParam date_from string required Data inicial (formato: Y-m-d). No-example
     * @bodyParam date_to string required Data final (formato: Y-m-d). No-example
     *
     * @response {
     *   "data": [
     *     {"reading_datetime": "2026-01-01 21:06:22", "water_level": "32", "flow": "1800.000000", "rain": "641.2", "water_temperature": "31.6", "atmospheric_pressure": "961.2"},
     *     {"reading_datetime": "2026-01-01 20:06:22", "water_level": "32", "flow": "1800.000000", "rain": "641.2", "water_temperature": "32.3", "atmospheric_pressure": "960.8"},
     *     {"reading_datetime": "2026-01-01 19:06:22", "water_level": "32", "flow": "1800.000000", "rain": "641.2", "water_temperature": "33.0", "atmospheric_pressure": "959.3"}
     *   ],
     *   "meta": {"station_code": "B04041E0", "total": 21, "period": {"from": "2026-01-01", "to": "2026-01-31"}, "retrieved_at": "2026-04-13T14:02:22-03:00"}
     * }
    */
    public function readings(Request $request, string $stationCode): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => ['required', 'date_format:Y-m-d'],
            'date_to'   => ['required', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $station = $this->stationRepository->findByDcpAddress($stationCode);

        if (!$station) {
            return response()->json(['error' => 'Estação não encontrada.'], 404);
        }

        $dateFrom = $validated['date_from'];
        $dateTo   = $validated['date_to'];

        $cursor = $this->readingService->cursorReadingsByAddressAndDateRange(
            $stationCode,
            $dateFrom . ' 00:00:00',
            $dateTo . ' 23:59:59'
        );

        $data  = [];
        $total = 0;

        foreach ($cursor as $reading) {
            $data[] = (new LrgsReadingResource($reading))->toArray($request);
            $total++;
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'station_code' => $stationCode,
                'total'        => $total,
                'period'       => [
                    'from' => $dateFrom,
                    'to'   => $dateTo,
                ],
                'retrieved_at' => now()->toIso8601String(),
            ],
        ]);
    }


}
