<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CnarhService;
use App\Http\Resources\CnarhResource;
use Illuminate\Http\JsonResponse;

class CnarhController extends Controller
{
    protected $service;

    public function __construct(CnarhService $service)
    {
        $this->service = $service;
    }

    public function getReadings(string $intCdCnarh40): JsonResponse
    {
        $cnarh = $this->service->getByCnarh($intCdCnarh40);

        if (!$cnarh) {
            return response()->json([
                'success' => false,
                'message' => 'Registro CNARH não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'int_cd_cnarh40' => $intCdCnarh40,
                'cnarh' => new CnarhResource($cnarh)
            ]
        ]);
    }
}