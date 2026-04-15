<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\App\PocoSiagasResource;
use App\Services\PocoSiagasService;
use Illuminate\Http\JsonResponse;

class PocoSiagasController extends Controller
{
    protected $service;

    public function __construct(PocoSiagasService $service)
    {
        $this->service = $service;
    }

    public function getReadings(string $idPonto): JsonResponse
    {
        $poco = $this->service->getByIdPonto($idPonto);

        if (!$poco) {
            return response()->json([
                'success' => false,
                'message' => 'Poço não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_ponto' => $idPonto,
                'poco' => new PocoSiagasResource($poco)
            ]
        ]);
    }
} 