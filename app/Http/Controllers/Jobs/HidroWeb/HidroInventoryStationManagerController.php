<?php

namespace App\Http\Controllers\Jobs\HidroWeb;

use App\Http\Controllers\Controller;
use App\Services\API_Hidroweb\HidrowebService;
use App\Services\HidroInventoryStationService;
use App\Services\HidroStationQaImportService;
use Illuminate\Http\JsonResponse;
use Log;

class HidroInventoryStationManagerController extends Controller
{
    protected HidrowebService $apiHidrowebService;
    protected HidroInventoryStationService $hidroInventoryStationService;
    protected HidroStationQaImportService $hidroStationQaImportService;

    public function __construct(
        HidrowebService $apiHidrowebService,
        HidroInventoryStationService $hidroInventoryStationService,
        HidroStationQaImportService $hidroStationQaImportService
    ) {
        $this->apiHidrowebService = $apiHidrowebService;
        $this->hidroInventoryStationService = $hidroInventoryStationService;
        $this->hidroStationQaImportService = $hidroStationQaImportService;
    } 

    /**
     * Fetch Hydro HidroInventoryStation data.
     *
     * @return JsonResponse
     */
    public function index()
    {   
        ignore_user_abort();
        ini_set('max_execution_time', 0);
        ini_set("memory_limit",-1);
        
        $maxAttempts = 100; // defina o número máximo de tentativas, se desejar
        $attempts = 0;
        
        while ($attempts < $maxAttempts) {
            try {
                $this->manageHidroInventoryStationData();
                // Se chegou até aqui sem exceção, retorne sucesso
                return response()->json(['status' => 'success'], 200);
            } catch (\Exception $e) {
                $attempts++;
                // Espera 30 segundos antes da próxima tentativa
                sleep(30);
            }
        }
        
        // Se chegou aqui, falhou após X tentativas
        return response()->json([
            'status' => 'error',
            'message' => 'Falha ao executar manageHidroInventoryStationData após várias tentativas.'
        ], 500);
    }

    /**
     * Manage Hydro HidroInventoryStation data.
     *
     * @return JsonResponse
     */
    public function manageHidroInventoryStationData() 
    {   
        $data = $this->apiHidrowebService->fetchHidroInventarioEstacoes();

        $stationsQaImport = $this->hidroStationQaImportService->getAll();
        $stationsQaImport = $stationsQaImport->pluck('station_code')->toArray();

        $inventoryStations = [];

        //$count = 0;
        foreach($data['items'] as $station) {
            //Log::info('['.++$count.'] Station Code: ' . $station['codigoestacao'] . ' - Operando: ' . $station['Operando'] . ' - Tipo_Estacao_Telemetrica: ' . $station['Tipo_Estacao_Telemetrica'] . ' - Tipo_Estacao_Qual_Agua: ' . $station['Tipo_Estacao_Qual_Agua']);
            
            // CORREÇÃO: usar 'codigoestacao' ao invés de 'Codigo_Estacao'
            // if (($station['Operando'] == "1" && $station['Tipo_Estacao_Telemetrica'] == "1") ||
            //     (in_array($station['codigoestacao'], $stationsQaImport) && $station['Tipo_Estacao_Qual_Agua'] == "1")
            // ) {
            //     $inventoryStations[] = $station;
            // }

            if (in_array($station['codigoestacao'], $stationsQaImport)) {
                $inventoryStations[] = $station;
            }
        } 

        Log::info("Total de estações filtradas: " . count($inventoryStations));

        $this->hidroInventoryStationService->update($inventoryStations);

        return true; 
    }
}
