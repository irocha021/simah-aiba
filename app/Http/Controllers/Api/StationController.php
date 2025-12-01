<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class StationController extends Controller
{
    public function __construct(
        protected StationService $stationService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $stations = $this->stationService->getAllForMap();

            return response()->json([
                'data' => $stations
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching unified stations: ' . $e->getMessage());

            return response()->json([
                'error' => 'Erro ao buscar estações',
            ], 500);
        }
    }
}
