<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PocoSiagasResource;
use App\Http\Resources\Public\PocoSiagasDetailResource;
use App\Repositories\Interfaces\PocoSiagasRepositoryInterface;
use Illuminate\Http\JsonResponse;

class PocoSiagasController extends Controller
{
    public function __construct(
        protected PocoSiagasRepositoryInterface $repository
    ) {}

    /**
     * Listar poços SIAGAS
     *
     * Retorna a lista de todos os poços cadastrados no SIAGAS.
     *
     * @group Poços SIAGAS
     *
     * @response {
     *   "data": [
     *     {"ponto": 2900000054, "localizacao": "BEZERRA", "latitude": "-12.225000000000000", "longitude": "-44.816388000000003"},
     *     {"ponto": 2900000151, "localizacao": "SANTO ANTONIO", "latitude": "-12.208888000000000", "longitude": "-44.988332999999997"},
     *     {"ponto": 2900000152, "localizacao": "PROJETO BARREIRAS SUL", "latitude": "-12.163055999999999", "longitude": "-44.710000000000001"}
     *   ],
     *   "meta": {"total": 3, "retrieved_at": "2026-04-13T13:55:03-03:00"}
     * }
     */
    public function wells(): JsonResponse
    {
        $wells = $this->repository->getAllWithCoordinates();

        return response()->json([
            'data' => PocoSiagasResource::collection($wells),
            'meta' => [
                'total'        => $wells->count(),
                'retrieved_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Dados do poço
     *
     * Retorna os dados de um poço SIAGAS pelo número do ponto.
     *
     * @group Poços SIAGAS
     *
     * @urlParam ponto int required Número do ponto do poço.  No-example
     *
     * @response {
     *   "data": {
     *     "ponto": 2900000152,
     *     "localizacao": "PROJETO BARREIRAS SUL",
     *     "latitude": "-12.163055999999999",
     *     "longitude": "-44.710000000000001",
     *     "utme": 531550,
     *     "utmn": 8655394,
     *     "bacia": "Rio São Francisco",
     *     "municipio": "Barreiras",
     *     "natureza": "Poço tubular",
     *     "nome": "CERB 1-730/78",
     *     "subbacia": "Rios São Francisco, Grande e outros",
     *     "uf": "BA",
     *     "data_perfuracao": "03/07/1978",
     *     "profundida": false,
     *     "profundidade_total": "80.000000000000000",
     *     "data_teste": "18/08/1978",
     *     "surgencia": "N",
     *     "nivel_dinamico": "27.980000000000000",
     *     "nivel_estatico": "12.940000000000000",
     *     "vazao_especifica": "1.072000000000000",
     *     "vazao_estabilizada": "16.129999999999999"
     *   }
     * }
     */
    public function show(int $ponto): JsonResponse
    {
        $well = $this->repository->findByPonto($ponto);

        if (!$well) {
            return response()->json(['error' => 'Poço não encontrado.'], 404);
        }

        return response()->json([
            'data' => new PocoSiagasDetailResource($well),
        ]);
    }
}
