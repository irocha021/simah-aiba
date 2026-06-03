<?php

namespace App\Http\Controllers\Jobs\HidroWeb;

use App\Enums\Job;
use App\Enums\JobStatus as EnumsJobStatus;
use App\Http\Controllers\Controller;
use App\Services\HidroStationFlowForecastService;
use App\Services\HidroStationReadingTelemetryService;
use App\Services\FlowForecastPythonService;
use App\Services\JobStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HidroFlowForecastController extends Controller
{
    public function __construct(
        protected HidroStationFlowForecastService $forecastService,
        protected HidroStationReadingTelemetryService $telemetryService,
        protected FlowForecastPythonService $pythonService,
        protected JobStatusService $jobStatusService
    ) {}

    public function index(Request $request)
    {
        ignore_user_abort();
        ini_set('max_execution_time', 0);
        ini_set("memory_limit", -1);

        // Validação: aceita parâmetro 'month' ou detecta mês atual
        $currentMonth = (int) Carbon::now()->format('m');
        $requestedMonth = $request->input('month', $currentMonth);

        // Validar se é maio (5), junho (6) ou julho (7)
        if (!in_array($requestedMonth, [5, 6, 7])) {
            return response()->json([
                'error' => 'Este job só pode ser executado para os meses de maio (5), junho (6) ou julho (7)',
                'month_received' => $requestedMonth
            ], 400);
        }

        $dataMonth = $requestedMonth - 1; // Mês dos dados (abril, maio, junho)
        $forecastMonth = $dataMonth; // Previsão é PARA o mês dos dados
        $forecastYear = (int) Carbon::now()->format('Y');

        Log::info("Iniciando job de previsão de vazão", [
            'requested_month' => $requestedMonth,
            'data_month' => $dataMonth,
            'forecast_month' => $forecastMonth,
            'forecast_year' => $forecastYear
        ]);

        // Criar status do job
        $jobStatus = $this->jobStatusService->store([
            'job' => Job::HW_FLOW_FORECAST ?? 'hw_flow_forecast',
            'datetime_reading' => Carbon::now()->format('Y-m-d'),
            'status' => EnumsJobStatus::PENDING,
        ]);

        // Buscar estações com parâmetros preenchidos
        $stations = DB::table('hw_inventory_station_data')
            ->whereNotNull('alfa_pond')
            ->whereNotNull('q_noventa')
            ->whereNotNull('vsup')
            ->get();

        Log::info("Estações encontradas com parâmetros", ['count' => $stations->count()]);

        $errorLogs = [];
        $successCount = 0;

        foreach ($stations as $stationData) {
            try {
                $this->processStationForecast($stationData, $dataMonth, $forecastMonth, $forecastYear);
                $successCount++;
            } catch (\Exception $e) {
                Log::error("Erro ao processar estação {$stationData->station_code}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $errorLogs[] = [
                    'station_code' => $stationData->station_code,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Atualizar status do job
        $this->jobStatusService->update($jobStatus->id, [
            'status' => EnumsJobStatus::OK,
            'logs' => json_encode([
                'total_stations' => $stations->count(),
                'success' => $successCount,
                'errors' => $errorLogs,
            ]),
        ]);

        return response()->json([
            'message' => 'Job de previsão de vazão concluído',
            'total_stations' => $stations->count(),
            'success' => $successCount,
            'errors' => $errorLogs,
        ], 200);
    }

    private function processStationForecast(object $stationData, int $dataMonth, int $forecastMonth, int $forecastYear): void
    {
        $stationCode = $stationData->station_code;

        Log::info("Processando estação", [
            'station_code' => $stationCode,
            'data_month' => $dataMonth,
            'forecast_month' => $forecastMonth
        ]);

        // Buscar leituras do mês anterior
        $startDate = Carbon::create($forecastYear, $dataMonth, 1)->startOfMonth();
        $endDate = Carbon::create($forecastYear, $dataMonth, 1)->endOfMonth();

        $readings = DB::table('hw_station_readings_telemetry')
            ->where('station_code', $stationCode)
            ->whereBetween('measurement_datetime', [$startDate, $endDate])
            ->orderBy('measurement_datetime', 'asc')
            ->get();

        if ($readings->isEmpty()) {
            Log::warning("Estação sem dados de telemetria para o mês", [
                'station_code' => $stationCode,
                'month' => $dataMonth
            ]);
            return; // Pular estação sem dados
        }

        Log::info("Leituras encontradas", [
            'station_code' => $stationCode,
            'count' => $readings->count()
        ]);

       
        // Deletar previsão anterior (hard delete)
        $this->forecastService->deleteByStationYearMonth($stationCode, $forecastYear, $forecastMonth);
        Log::info("Previsão anterior deletada", [
            'station_code' => $stationCode,
            'forecast_year' => $forecastYear,
            'forecast_month' => $forecastMonth
        ]);

        // Gerar CSV temporário
        $csvPath = $this->pythonService->generateCsvFromReadings($readings->toArray(), $stationCode);

        try {
            // Executar script Python
            $result = $this->pythonService->executeForecast(
                $csvPath,
                $dataMonth, // Mês dos dados (abril, maio, junho)
                (float) $stationData->alfa_pond,
                (float) $stationData->q_noventa,
                (float) $stationData->vsup
            );

            Log::info("Script Python executado com sucesso", [
                'station_code' => $stationCode,
                'result' => $result
            ]);

            // Armazenar resultado no banco
            $this->forecastService->updateOrCreate(
                [
                    'station_code' => $stationCode,
                    'forecast_year' => $forecastYear,
                    'forecast_month' => $forecastMonth,
                ],
                [
                    'predicted_flow' => $result['vazao_prevista'],
                    'minimum_flow' => $result['vazao_minima'],
                    'forecast_start_day' => $result['dia_inicio'],
                    'alfa_pond' => $stationData->alfa_pond,
                    'q_noventa' => $stationData->q_noventa,
                    'vsup' => $stationData->vsup,
                ]
            );

            Log::info("Previsão armazenada com sucesso", [
                'station_code' => $stationCode,
                'predicted_flow' => $result['vazao_prevista']
            ]);

        } finally {
            // Sempre remover CSV temporário
            $this->pythonService->cleanupCsv($csvPath);
        }
    }
    
    /**
     * API: Retorna previsões de vazão para uma estação específica
     * Endpoint: GET /api/hidroweb-telemetria/{station_code}/forecast
     */
    public function getForecastForStation(string $stationCode)
    {
        try {
            // Buscar previsões da estação ordenadas por data
            $forecasts = DB::table('hw_station_flow_forecasts')
                ->where('station_code', $stationCode)
                ->orderBy('forecast_year', 'desc')
                ->orderBy('forecast_month', 'desc')
                ->limit(30)
                ->get();

            if ($forecasts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma previsão encontrada para esta estação',
                ], 404);
            }

            // Formatar dados para o frontend
            $formattedForecasts = $forecasts->map(function ($forecast) {
                return [
                    'forecast_year' => $forecast->forecast_year,
                    'forecast_month' => str_pad($forecast->forecast_month, 2, '0', STR_PAD_LEFT),
                    'predicted_flow' => number_format((float) $forecast->predicted_flow, 2, ',', '.'),
                    'alfa_pond' => number_format((float) $forecast->alfa_pond, 8, ',', '.'),
                    'q_noventa' => number_format((float) $forecast->q_noventa, 5, ',', '.'),
                    'vsup' => number_format((float) $forecast->vsup, 2, ',', '.'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'forecasts' => $formattedForecasts,
                    'total' => $forecasts->count(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error("Erro ao buscar previsões", [
                'station_code' => $stationCode,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar previsões: ' . $e->getMessage(),
            ], 500);
        }
    }

}
