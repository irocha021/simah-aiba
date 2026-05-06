<?php

namespace App\Http\Controllers\Jobs\Lrgs;

use App\Http\Controllers\Controller;
use App\Models\DcpStation;
use App\Models\DcpSyncLog;
use App\Services\DcpStationService;
use App\Services\DcpSyncLogService;
use App\Services\Lrgs\DcpJobLogger;
use App\Services\Lrgs\DcpReadingService;
use App\Services\Lrgs\LrgsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReadDcpMessagesController extends Controller
{
    private LrgsService $lrgsService;
    private DcpReadingService $dcpReadingService;
    private DcpStationService $dcpStationService;
    private DcpSyncLogService $dcpSyncLogService;
    private DcpJobLogger $jobLogger;

    public function __construct(
        LrgsService $lrgsService,
        DcpReadingService $dcpReadingService,
        DcpStationService $dcpStationService,
        DcpSyncLogService $dcpSyncLogService,
        DcpJobLogger $jobLogger
    ) {
        $this->lrgsService = $lrgsService;
        $this->dcpReadingService = $dcpReadingService;
        $this->dcpStationService = $dcpStationService;
        $this->dcpSyncLogService = $dcpSyncLogService;
        $this->jobLogger = $jobLogger;
    }


    /**
     * Retrieve messages for current period (last full hour)
     */
    public function retrieveMessages(): JsonResponse
    {
        $invocationId = $this->jobLogger->newInvocationId();
        $jobStartedAt = microtime(true);

        // Guarda de concorrência: aborta se houver outro job 'running' iniciado < 10min
        $running = DcpSyncLog::where('status', 'running')
            ->where('started_at', '>=', now()->subMinutes(10))
            ->get();

        if ($running->isNotEmpty()) {
            $this->jobLogger->jobAlreadyRunning($invocationId, $running->pluck('id')->all());
            return response()->json([
                'success' => false,
                'reason'  => 'already_running',
                'invocation_id' => $invocationId,
                'running_sync_logs' => $running->pluck('id'),
                'message' => 'Outro job DCP iniciado há menos de 10 minutos ainda está em execução'
            ], 409);
        }

        try {
            $stations = (object) $this->dcpStationService->getActiveStations();

            if ($stations->isEmpty()) {
                $this->jobLogger->jobStart($invocationId, ['active_stations' => 0]);
                $this->jobLogger->jobEnd($invocationId, ['reason' => 'no_active_stations']);
                return response()->json([
                    'success' => false,
                    'error' => 'Nenhuma estação ativa encontrada'
                ], 404);
            }

            $interval = $this->lrgsService->getLastFullHourInterval();

            $this->jobLogger->jobStart($invocationId, [
                'active_stations' => count($stations),
                'interval' => sprintf('%s → %s',
                    $interval['start']->toDateTimeString(),
                    $interval['end']->toDateTimeString()
                ),
                'caller_ip' => request()->ip(),
            ]);

            $totalMessages = 0;
            $totalInserted = 0;
            $totalCorrupted = 0;
            $totalSkipped = 0;
            $stationsStats = [];

            $beforeDelimiter = config('lrgs.delimiters.before');

            foreach ($stations as $station) {
                $syncLog = $this->dcpSyncLogService->createLog(
                    $station->id,
                    $interval['start'],
                    $interval['end']
                );

                try {
                    $this->jobLogger->stationStart(
                        $invocationId,
                        $syncLog->id,
                        $station,
                        $interval['start']->toDateTimeString(),
                        $interval['end']->toDateTimeString()
                    );

                    $this->dcpSyncLogService->startLog($syncLog->id);

                    $this->lrgsService->generateSearchCriteria(
                        $station->dcp_address,
                        $interval['start'],
                        $interval['end']
                    );

                    $filePath = $this->lrgsService->fetchMessages();
                    $fileContent = file_get_contents($filePath);

                    $parts = explode($beforeDelimiter, $fileContent);

                    $messages = array_filter($parts, function($part) {
                        return !empty(trim($part));
                    });

                    $filteredMessages = array_filter($messages, function($line) {
                        $line = trim($line);
                        return !empty($line) &&
                            !str_contains($line, 'Normal termination') &&
                            !str_contains($line, 'Until time reached') &&
                            !str_contains($line, 'Missing message') &&
                            !str_contains($line, 'Wrong channel') &&
                            !str_contains($line, 'TESTE DE TRANSMISSAO');
                    });

                    $stats = $this->dcpReadingService->processAndInsertMessages(
                        array_values($filteredMessages),
                        $syncLog->id,
                        $invocationId
                    );

                    $this->dcpSyncLogService->completeLog(
                        $syncLog->id,
                        $stats['total'],
                        $stats['inserted'],
                        $stats['corrupted'],
                        $stats['corrupted_headers']
                    );

                    $totalMessages += $stats['total'];
                    $totalInserted += $stats['inserted'];
                    $totalCorrupted += $stats['corrupted'];
                    $totalSkipped += $stats['skipped_duplicate'] ?? 0;

                    $stationsStats[] = [
                        'station_id' => $station->id,
                        'name' => $station->name,
                        'address' => $station->dcp_address,
                        'messages' => $stats['total'],
                        'inserted' => $stats['inserted'],
                        'skipped_duplicate' => $stats['skipped_duplicate'] ?? 0,
                        'corrupted' => $stats['corrupted'],
                        'file' => $filePath,
                        'sync_log_id' => $syncLog->id
                    ];

                    $this->jobLogger->stationDone($invocationId, $syncLog->id, [
                        'total'    => $stats['total'],
                        'inserted' => $stats['inserted'],
                        'skipped'  => $stats['skipped_duplicate'] ?? 0,
                        'corrupted'=> $stats['corrupted'],
                    ]);

                } catch (\Exception $e) {
                    $this->jobLogger->error($invocationId, 'station_processing', $e, [
                        'sync_log' => $syncLog->id,
                        'station_id' => $station->id,
                        'station_name' => $station->name,
                    ]);

                    $this->dcpSyncLogService->failLog($syncLog->id, $e->getMessage());

                    $stationsStats[] = [
                        'station_id' => $station->id,
                        'name' => $station->name,
                        'address' => $station->dcp_address,
                        'messages' => 0,
                        'inserted' => 0,
                        'skipped_duplicate' => 0,
                        'corrupted' => 0,
                        'error' => $e->getMessage(),
                        'sync_log_id' => $syncLog->id
                    ];

                    continue;
                }
            }

            $this->reprocessStuckJobs($invocationId);

            $duration = round(microtime(true) - $jobStartedAt, 2);
            $this->jobLogger->jobEnd($invocationId, [
                'stations'  => count($stations),
                'messages'  => $totalMessages,
                'inserted'  => $totalInserted,
                'skipped'   => $totalSkipped,
                'corrupted' => $totalCorrupted,
                'duration_s'=> $duration,
            ]);

            return response()->json([
                'success' => true,
                'invocation_id' => $invocationId,
                'interval' => [
                    'start' => $interval['start']->toDateTimeString(),
                    'end' => $interval['end']->toDateTimeString()
                ],
                'total_stations' => count($stations),
                'total_messages' => $totalMessages,
                'total_inserted' => $totalInserted,
                'total_skipped_duplicate' => $totalSkipped,
                'total_corrupted' => $totalCorrupted,
                'stations' => $stationsStats,
                'summary' => [
                    'processed' => $totalMessages,
                    'valid' => $totalInserted,
                    'skipped' => $totalSkipped,
                    'invalid' => $totalCorrupted,
                ]
            ]);

        } catch (\Exception $e) {
            $this->jobLogger->error($invocationId, 'job_top_level', $e);

            return response()->json([
                'success' => false,
                'invocation_id' => $invocationId,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Reprocess stuck or failed jobs (max 3 attempts)
     * Called at the end of normal processing
     */
    private function reprocessStuckJobs(string $invocationId): void
    {
        try {
            $pendingForRetry = (object) $this->dcpSyncLogService->findPendingForRetry();

            if ($pendingForRetry->isEmpty()) {
                return;
            }

            $this->jobLogger->reprocessStart($invocationId, $pendingForRetry->count());

            foreach ($pendingForRetry as $syncLog) {
                try {
                    if (!$this->dcpSyncLogService->canRetry($syncLog->id)) {
                        continue;
                    }

                    $station = $this->dcpStationService->getActiveStations()
                        ->firstWhere('id', $syncLog->dcp_station_id);

                    if (!$station) {
                        $this->dcpSyncLogService->failLog(
                            $syncLog->id,
                            'Estação não encontrada ou inativa'
                        );
                        continue;
                    }

                    $this->dcpReadingService->deleteByStationAndPeriod(
                        $syncLog->dcp_station_id,
                        $syncLog->start_time,
                        $syncLog->end_time
                    );

                    $this->dcpSyncLogService->startLog($syncLog->id);

                    $this->lrgsService->generateSearchCriteria(
                        $station->dcp_address,
                        $syncLog->start_time,
                        $syncLog->end_time
                    );

                    $filePath = $this->lrgsService->fetchMessages();
                    $fileContent = file_get_contents($filePath);

                    $beforeDelimiter = config('lrgs.delimiters.before');
                    $parts = explode($beforeDelimiter, $fileContent);

                    $messages = array_filter($parts, function($part) {
                        return !empty(trim($part));
                    });

                    $filteredMessages = array_filter($messages, function($line) {
                        $line = trim($line);
                        return !empty($line) &&
                            !str_contains($line, 'Normal termination') &&
                            !str_contains($line, 'Until time reached') &&
                            !str_contains($line, 'Missing message') &&
                            !str_contains($line, 'Wrong channel') &&
                            !str_contains($line, 'TESTE DE TRANSMISSAO');
                    });

                    $stats = $this->dcpReadingService->processAndInsertMessages(
                        array_values($filteredMessages),
                        $syncLog->id,
                        $invocationId
                    );

                    $this->dcpSyncLogService->completeLog(
                        $syncLog->id,
                        $stats['total'],
                        $stats['inserted'],
                        $stats['corrupted'],
                        $stats['corrupted_headers']
                    );

                } catch (\Exception $e) {
                    $this->jobLogger->error($invocationId, 'reprocess_sync_log', $e, [
                        'sync_log' => $syncLog->id,
                    ]);

                    $this->dcpSyncLogService->failLog($syncLog->id, $e->getMessage());
                }
            }

            $this->jobLogger->reprocessDone($invocationId, $pendingForRetry->count());

        } catch (\Exception $e) {
            $this->jobLogger->error($invocationId, 'reprocess_top_level', $e);
        }
    }


    /**
     * Manual reprocessing for specific station and time period
     * 
     * Sample usage: http://{domain}/jobs/lrgs/readings/dcp-messages/manual?station_id=1&start_time=2026-02-17+00:00:00&end_time=2026-02-18+23:59:59
     * http://{domain}/jobs/lrgs/readings/dcp-messages/manual?station_id=1&start_time=2026-02-01+00:00:00&end_time=2026-02-18+23:59:59
     */
    public function retrieveMessagesManual(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'station_id' => 'required|integer|exists:dcp_stations,id',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
            ]);

            $stationId = $request->input('station_id');
            $startTime = \Carbon\Carbon::parse($request->input('start_time'), 'America/Bahia')->utc();
            $endTime = \Carbon\Carbon::parse($request->input('end_time'), 'America/Bahia')->utc();

            // Busca a estação
            $station = $this->dcpStationService->getActiveStations()
                ->firstWhere('id', $stationId);

            if (!$station) {
                return response()->json([
                    'success' => false,
                    'error' => 'Estação não encontrada ou inativa'
                ], 404);
            }

            Log::info('Reprocessamento manual iniciado', [
                'station_id' => $stationId,
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString()
            ]);

            // Cria sync log
            $syncLog = $this->dcpSyncLogService->createLog($stationId, $startTime, $endTime);

            // Soft delete readings antigos do período
            $deletedCount = $this->dcpReadingService->deleteByStationAndPeriod(
                $stationId,
                $startTime,
                $endTime
            );

            Log::info('Readings antigos removidos', [
                'deleted_count' => $deletedCount,
                'sync_log_id' => $syncLog->id
            ]);

            // Marca como 'running'
            $this->dcpSyncLogService->startLog($syncLog->id);

            // Gera search criteria
            $this->lrgsService->generateSearchCriteria(
                $station->dcp_address,
                $startTime,
                $endTime
            );

            // Busca mensagens
            $filePath = $this->lrgsService->fetchMessages();
            $fileContent = file_get_contents($filePath);

            // Processa mensagens
            $beforeDelimiter = config('lrgs.delimiters.before');
            $parts = explode($beforeDelimiter, $fileContent);

            $messages = array_filter($parts, function($part) {
                return !empty(trim($part));
            });

            $filteredMessages = array_filter($messages, function($line) {
                $line = trim($line);
                return !empty($line) &&
                    !str_contains($line, 'Normal termination') &&
                    !str_contains($line, 'Until time reached') &&
                    !str_contains($line, 'Missing message') &&        // NOVO
                    !str_contains($line, 'Wrong channel') &&          // NOVO
                    !str_contains($line, 'TESTE DE TRANSMISSAO');     // NOVO (opcional)
            });

            $filteredMessages = array_reverse(array_values($filteredMessages));

            // Processa e insere
            $stats = $this->dcpReadingService->processAndInsertMessages(array_values($filteredMessages));

            // Completa o sync log
            $this->dcpSyncLogService->completeLog(
                $syncLog->id,
                $stats['total'],
                $stats['inserted'],
                $stats['corrupted'],
                $stats['corrupted_headers']
            );

            Log::info('Reprocessamento manual concluído', [
                'sync_log_id' => $syncLog->id,
                'messages' => $stats['total'],
                'inserted' => $stats['inserted'],
                'corrupted' => $stats['corrupted']
            ]);

            return response()->json([
                'success' => true,
                'sync_log_id' => $syncLog->id,
                'station' => [
                    'id' => $station->id,
                    'name' => $station->name,
                    'address' => $station->dcp_address
                ],
                'period' => [
                    'start' => $startTime->toDateTimeString(),
                    'end' => $endTime->toDateTimeString()
                ],
                'deleted_old_readings' => $deletedCount,
                'statistics' => [
                    'total_messages' => $stats['total'],
                    'inserted' => $stats['inserted'],
                    'corrupted' => $stats['corrupted']
                ],
                'file' => $filePath
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados de entrada inválidos',
                'validation_errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Erro no reprocessamento manual', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($syncLog)) {
                $this->dcpSyncLogService->failLog($syncLog->id, $e->getMessage());
            }

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
