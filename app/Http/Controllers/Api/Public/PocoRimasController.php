<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PocoRimasResource;
use App\Http\Resources\Public\PocoRimasReadingResource;
use App\Repositories\Interfaces\PocoRimasRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PocoRimasController extends Controller
{
    public function __construct(
        protected PocoRimasRepositoryInterface $repository
    ) {}

    /**
     * Listar pontos RIMAS
     *
     * Retorna a lista de pontos de monitoramento da rede RIMAS.
     *
     * @group Poços RIMAS
     *
     * @response {
     *   "data": [
     *     {"id_ponto": 2900020672, "latitude": "-12.165556000000000", "longitude": "-45.328611000000002"},
     *     {"id_ponto": 2900020673, "latitude": "-12.299443999999999", "longitude": "-45.449722000000001"},
     *     {"id_ponto": 2900020674, "latitude": "-12.179167000000000", "longitude": "-45.752222000000003"}
     *   ],
     *   "meta": {"total": 3, "retrieved_at": "2026-04-13T13:55:03-03:00"}
     * }
     */
    public function points(): JsonResponse
    {
        $points = $this->repository->getAllWithCoordinates();

        return response()->json([
            'data' => PocoRimasResource::collection($points),
            'meta' => [
                'total'        => $points->count(),
                'retrieved_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Leituras do ponto RIMAS
     *
     * Retorna as leituras de um ponto RIMAS pelo id do ponto.
     * Se não informar as datas, retorna todas as leituras.
     *
     * @group Poços RIMAS
     *
     * @urlParam id_ponto int required ID do ponto. No-example
     * @queryParam date_from string opcional Data inicial (formato: Y-m-d). No-example
     * @queryParam date_to string opcional Data final (formato: Y-m-d). No-example
     * @bodyParam date_from string opcional Data inicial (formato: Y-m-d). No-example
     * @bodyParam date_to string opcional Data final (formato: Y-m-d). No-example
     *
     * @response {
     *   "data": [
     *     {"numero_medicao": 1, "data": "23/09/2015", "hora": "19:00:00", "nivel_agua": "8.789999999999999", "observacao": null},
     *     {"numero_medicao": 2, "data": "24/09/2015", "hora": "02:00:00", "nivel_agua": "8.800000000000001", "observacao": null},
     *     {"numero_medicao": 3, "data": "25/09/2015", "hora": "01:00:00", "nivel_agua": "8.800000000000001", "observacao": null}
     *   ],
     *   "meta": {"id_ponto": 2900020673, "total": 3, "period": {"from": null, "to": null}, "retrieved_at": "2026-04-13T13:55:52-03:00"}
     * }
     */
    public function readings(Request $request, int $idPonto): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to'   => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $validated['date_from'] ?? null;
        $dateTo   = $validated['date_to'] ?? null;

        $readings = $this->repository->getReadingsByIdPontoAndDateRange(
            $idPonto,
            $dateFrom,
            $dateTo
        );

        return response()->json([
            'data' => PocoRimasReadingResource::collection($readings),
            'meta' => [
                'id_ponto'     => $idPonto,
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
