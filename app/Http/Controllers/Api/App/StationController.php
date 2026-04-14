<?php

namespace App\Http\Controllers\Api\App;

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

    public function getCnarh(): JsonResponse
    {

        
        try {
            $stations = $this->stationService->getCnarhStations();

            return response()->json([
                'data' => ['stations' => $stations, 'count' => count($stations)]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching CNARH stations: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar estações CNARH'], 500);
        }
    }

    public function getPocosRimas(): JsonResponse
    {
        try {
            $stations = $this->stationService->getPocosRimasStations();
            return response()->json([
                'data' => ['stations' => $stations, 'count' => count($stations)]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Poços RIMAS stations: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar estações Poços RIMAS'], 500);
        }
    }

    public function getPocosSiagas(): JsonResponse
    {
        try {
            $stations = $this->stationService->getPocosSiagasStations();
            return response()->json([
                'data' => ['stations' => $stations, 'count' => count($stations)]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Poços SIAGAS stations: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar estações Poços SIAGAS'], 500);
        }
    }

    public function getHidrowebTelemetria(): JsonResponse
    {
        try {
            $stations = $this->stationService->getHidrowebTelemetriaStations();
            return response()->json([
                'data' => ['stations' => $stations, 'count' => count($stations)]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching HidroWeb Telemetria stations: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar estações HidroWeb Telemetria'], 500);
        }
    }

    public function getHidrowebQualidadeAgua(): JsonResponse
    {
        try {
            $stations = $this->stationService->getHidrowebQualidadeAguaStations();
            return response()->json([
                'data' => ['stations' => $stations, 'count' => count($stations)]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching HidroWeb Qualidade da Água stations: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar estações HidroWeb Qualidade da Água'], 500);
        }
    }

    public function getHidrowebTelemetriaPrevisao(): JsonResponse
    {
        try {
            $stations = $this->stationService->getHidrowebTelemetriaPrevisaoStations();
            return response()->json([
                'data' => ['stations' => $stations, 'count' => count($stations)]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching HidroWeb Telemetria com Previsão stations: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar estações HidroWeb Telemetria com Previsão'], 500);
        }
    }

    public function getLrgsClient(): JsonResponse
    {
        try {
            $stations = $this->stationService->getLrgsClientStations();
            return response()->json([
                'data' => ['stations' => $stations, 'count' => count($stations)]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching LRGS Client stations: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar estações LRGS Client'], 500);
        }
    }

}
