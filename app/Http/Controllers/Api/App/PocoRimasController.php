<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\App\PocoRimasResource;
use App\Services\PocoRimasService;
use Illuminate\Http\JsonResponse;

class PocoRimasController extends Controller
{   
    protected $service;

    public function __construct(PocoRimasService $service) {
        $this->service = $service;
    }

    public function getReadings(string $id_ponto): JsonResponse
    {
        $readings = $this->service->getReadingsByIdPonto($id_ponto, 50);

        return (new PocoRimasResource($readings))
            ->additional([
                'success' => true,
                'meta' => [
                    'id_ponto' => $id_ponto,
                    'total' => $readings->count()
                ]
            ])
            ->response();
    }
}
