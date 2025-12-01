<?php

namespace App\Http\Controllers\Jobs\HidroWeb;

use App\Enums\Job;
use App\Enums\JobStatus as EnumsJobStatus;
use App\Http\Controllers\Controller;
use App\Services\HidroStationReadingTelemetryService;
use App\Services\API_Hidroweb\HidrowebService;
use App\Services\HidroStationTelemetryImportService;
use App\Services\JobStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HidroInfoAnaAdoptedTelemetricSeriesReadingController extends Controller
{
    protected HidroStationTelemetryImportService $hidroStationTelemetryImportService;
    protected HidrowebService $apiHidroweb;
    protected HidroStationReadingTelemetryService $hidroStationReadingTelemetryService;
    protected JobStatusService $jobStatusService;

    public function __construct(
        HidroStationTelemetryImportService $hidroStationTelemetryImportService,
        HidrowebService $apiHidroweb,
        HidroStationReadingTelemetryService $hidroStationReadingTelemetryService,
        JobStatusService $jobStatusService
    ) {
        $this->hidroStationTelemetryImportService = $hidroStationTelemetryImportService;
        $this->apiHidroweb = $apiHidroweb;
        $this->hidroStationReadingTelemetryService = $hidroStationReadingTelemetryService;
        $this->jobStatusService = $jobStatusService;
    }

    public function index(Request $request)
    {
        ignore_user_abort();
        ini_set('max_execution_time', 0);
        ini_set("memory_limit", -1);

        $date = $request->input('date', Carbon::now()->subDay()->format('Y-m-d'));

        // Delete existing readings for the date
        $this->hidroStationReadingTelemetryService->deleteByDate($date);

        // Create job status
        $jobStatus = $this->jobStatusService->store([
            'job' => Job::HW_STATION_READING,
            'datetime_reading' => $date,
            'status' => EnumsJobStatus::PENDING,
        ]);


        $stations = $this->hidroStationTelemetryImportService->getAll();

      
        $errorLogs = []; // Array to store error logs

        foreach ($stations as $station) {
            try {
                $this->processStationData($station, $date);
            } catch (\Exception $e) {
                Log::error("Erro ao processar estação {$station->station_code}: {$e->getMessage()}");

                // Add error to error logs
                $errorLogs[] = [
                    'station_code' => $station->station_code,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Update job status with errors (if any)
        $this->jobStatusService->update(
            $jobStatus->id,
            [
                'status' => EnumsJobStatus::OK,
                'logs' => json_encode($errorLogs), // Save errors as JSON
            ]
        );

        return response()->json(['errors' => $errorLogs], 200);
    }

    /**
     * Process station data for a specific station and date.
     *
     * @param object $station
     * @param string $date
     * @throws \Exception
     */
    private function processStationData(object $station, string $date): void
    {
        $items = [];
        $attempts = 0; // Contador de tentativas
        $maxAttempts = 10; // Máximo de tentativas permitido
        $success = false; // Flag para indicar se a requisição foi bem-sucedida

        while ($attempts < $maxAttempts && !$success) {
            try {

                
                // Tenta buscar os dados da API
                $reading = $this->apiHidroweb->fetchHidroinfoanaSerieTelemetricaAdotada([
                    'Código da Estação' => $station->station_code,
                    'Tipo Filtro Data' => 'DATA_LEITURA',
                    'Data de Busca (yyyy-MM-dd)' => $date,
                    'Range Intervalo de busca' => 'HORA_24',
                ]);

                if (isset($reading['error'])) {
                    Log::error("Erro ao chamar a API para a estação {$station->station_code}: {$reading['error']}");
                    throw new \Exception($reading['error']);
                }

                // Se a chamada da API foi bem-sucedida, marca como sucesso
                $success = true;

                // Processa os itens retornados, se houver
                if (isset($reading['items']) && count($reading['items']) > 0) {
                    foreach ($reading['items'] as $item) {
                        $items[] = [
                            'station_code' => $station->station_code,
                            'adopted_rainfall' => $item['Chuva_Adotada'],
                            'adopted_quota' => $item['Cota_Adotada'],
                            'adopted_flow' => $item['Vazao_Adotada'],
                            'measurement_datetime' => $item['Data_Hora_Medicao'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }

                    // Store readings
                    $this->hidroStationReadingTelemetryService->storeReadingsOfStation($items);
                }
            } catch (\Exception $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    Log::warning("Falha ao chamar a API para a estação {$station->station_code} após {$maxAttempts} tentativas.");
                    throw $e; // Relança a exceção após atingir o limite de tentativas
                }

                // Aguarda 30 segundos antes de tentar novamente
                sleep(30);
            }
        }
    }
}
