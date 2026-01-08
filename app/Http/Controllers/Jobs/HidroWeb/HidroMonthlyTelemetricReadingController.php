<?php

namespace App\Http\Controllers\Jobs\HidroWeb;

use App\Enums\Job;
use App\Enums\JobStatus as EnumsJobStatus;
use App\Http\Controllers\Controller;
use App\Services\HidroStationReadingTelemetryService;
use App\Services\API_Hidroweb\HidrowebService;
use App\Services\HidroInventoryStationService;
use App\Services\JobStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HidroMonthlyTelemetricReadingController extends Controller
{
    protected HidroInventoryStationService $hidroInventoryStationService;
    protected HidrowebService $apiHidroweb;
    protected HidroStationReadingTelemetryService $hidroStationReadingTelemetryService;
    protected JobStatusService $jobStatusService;

    public function __construct(
        HidroInventoryStationService $hidroInventoryStationService,
        HidrowebService $apiHidroweb,
        HidroStationReadingTelemetryService $hidroStationReadingTelemetryService,
        JobStatusService $jobStatusService
    ) {
        $this->hidroInventoryStationService = $hidroInventoryStationService;
        $this->apiHidroweb = $apiHidroweb;
        $this->hidroStationReadingTelemetryService = $hidroStationReadingTelemetryService;
        $this->jobStatusService = $jobStatusService;
    }

    public function index(Request $request)
    {
       
        ignore_user_abort();
        ini_set('max_execution_time', 0);
        ini_set("memory_limit", -1);

        // Validar os parâmetros
        $request->validate([
            'station_code' => 'required|string',
            'month' => 'required|regex:/^\d{4}-\d{1,2}$/', // Aceita Y-m ou Y-m com zero à esquerda
        ]);

        $stationCode = $request->input('station_code');
        $monthInput = $request->input('month'); // Formato: 2024-04 ou 2024-4

        // Normalizar o mês para o formato Y-m com zero à esquerda
        $monthParts = explode('-', $monthInput);
        $month = $monthParts[0] . '-' . str_pad($monthParts[1], 2, '0', STR_PAD_LEFT);

        // Criar período do mês
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        Log::info("Iniciando processamento mensal para estação {$stationCode} - Período: {$startDate->format('Y-m-d')} a {$endDate->format('Y-m-d')}");

        // Buscar a estação
        $station = $this->hidroInventoryStationService->getByStationCode($stationCode);
        
        if (!$station) {
            return response()->json([
                'error' => "Estação {$stationCode} não encontrada"
            ], 404);
        }

        // Array para armazenar os erros
        $errorLogs = [];
        $successCount = 0;

        // Iterar por cada dia do mês
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            
            try {
                Log::info("Processando dia {$dateStr} para estação {$stationCode}");
                
                // Deletar leituras existentes para este dia
                $this->hidroStationReadingTelemetryService->deleteByDate($dateStr);
                
                // Processar os dados do dia
                $this->processStationData($station, $dateStr);
                
                $successCount++;
                
            } catch (\Exception $e) {
                Log::error("Erro ao processar dia {$dateStr} para estação {$stationCode}: {$e->getMessage()}");
                
                $errorLogs[] = [
                    'station_code' => $stationCode,
                    'date' => $dateStr,
                    'error' => $e->getMessage(),
                ];
            }
            
            // Avançar para o próximo dia
            $currentDate->addDay();
        }

        // Criar job status final
        // $jobStatus = $this->jobStatusService->store([
        //     'job' => Job::HW_STATION_READING,
        //     'datetime_reading' => $month,
        //     'status' => count($errorLogs) > 0 ? EnumsJobStatus::PENDING : EnumsJobStatus::OK,
        // ]);

        // $this->jobStatusService->update(
        //     $jobStatus->id,
        //     [
        //         'status' => count($errorLogs) > 0 ? EnumsJobStatus::PENDING : EnumsJobStatus::OK,
        //         'logs' => json_encode([
        //             'total_days' => $startDate->diffInDays($endDate) + 1,
        //             'success_days' => $successCount,
        //             'error_days' => count($errorLogs),
        //             'errors' => $errorLogs
        //         ]),
        //     ]
        // );

        Log::info("Processamento mensal concluído - Sucessos: {$successCount}, Erros: " . count($errorLogs));

        return response()->json([
            'message' => 'Processamento mensal concluído',
            'total_days' => $startDate->diffInDays($endDate) + 1,
            'success_days' => $successCount,
            'error_days' => count($errorLogs),
            'errors' => $errorLogs
        ], 200);
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
        $attempts = 0;
        $maxAttempts = 10;
        $success = false;

        while ($attempts < $maxAttempts && !$success) {
            try {
                $reading = $this->apiHidroweb->fetchHidroinfoanaSerieTelemetricaAdotada([
                    'Código da Estação' => $station->station_code,
                    'Tipo Filtro Data' => 'DATA_LEITURA',
                    'Data de Busca (yyyy-MM-dd)' => $date,
                    'Range Intervalo de busca' => 'HORA_24',
                ]);

                if (isset($reading['error'])) {
                    Log::error("Erro ao chamar a API para a estação {$station->station_code}: {$reading['error']}");
                    
                    // Se for erro 401 ou 417, tenta renovar o token e fazer nova tentativa
                    if (isset($reading['code']) && ($reading['code'] === 401 || $reading['code'] === 417)) {
                        Log::info("Erro {$reading['code']} detectado - renovando token de autenticação...");
                        $this->apiHidroweb->authenticate();
                        
                        // Não lança exceção ainda, deixa o loop tentar novamente
                        throw new \Exception($reading['error']);
                    }
                    
                    throw new \Exception($reading['error']);
                }

                $success = true;

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

                    $this->hidroStationReadingTelemetryService->storeReadingsOfStation($items);
                }
            } catch (\Exception $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    Log::warning("Falha ao chamar a API para a estação {$station->station_code} após {$maxAttempts} tentativas.");
                    throw $e;
                }

                // Backoff exponencial: 30s, 60s, 120s, 240s, etc.
                $waitTime = 30 * pow(2, $attempts - 1);
                // Limita o tempo máximo de espera em 5 minutos
                $waitTime = min($waitTime, 300);

                Log::warning("Erro na tentativa {$attempts}. Aguardando {$waitTime} segundos antes de tentar novamente...");
                sleep($waitTime);
            }
        }
    }
}
