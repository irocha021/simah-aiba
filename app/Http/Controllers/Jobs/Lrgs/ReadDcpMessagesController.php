<?php

namespace App\Http\Controllers\Jobs\Lrgs;

use App\Http\Controllers\Controller;
use App\Models\DcpStation;
use App\Services\DcpStationService;
use App\Services\DcpSyncLogService;
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

    public function __construct(
        LrgsService $lrgsService,
        DcpReadingService $dcpReadingService,
        DcpStationService $dcpStationService,
        DcpSyncLogService $dcpSyncLogService
    ) {
        $this->lrgsService = $lrgsService;
        $this->dcpReadingService = $dcpReadingService;
        $this->dcpStationService = $dcpStationService;
        $this->dcpSyncLogService = $dcpSyncLogService;
    }

    /**
     * Retrieve messages for current period (last full hour)
     */
    public function retrieveMessages(): JsonResponse
    {
        try {
            // Busca estações ativas
            $stations = (object) $this->dcpStationService->getActiveStations();

            if ($stations->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nenhuma estação ativa encontrada'
                ], 404);
            }

            // Calcula intervalo da última hora cheia
            $interval = $this->lrgsService->getLastFullHourInterval();

            // Estatísticas consolidadas
            $totalMessages = 0;
            $totalInserted = 0;
            $totalCorrupted = 0;
            $stationsStats = [];

            // Define delimitadores
            $beforeDelimiter = config('lrgs.delimiters.before');

            // Loop por cada estação
            foreach ($stations as $station) {
                // Cria sync log em status 'pending'
                $syncLog = $this->dcpSyncLogService->createLog(
                    $station->id,
                    $interval['start'],
                    $interval['end']
                );

                try {
                    Log::info('Processando estação DCP', [
                        'station_id' => $station->id,
                        'station_name' => $station->name,
                        'dcp_address' => $station->dcp_address,
                        'sync_log_id' => $syncLog->id
                    ]);

                    // Marca como 'running' e incrementa attempts
                    $this->dcpSyncLogService->startLog($syncLog->id);

                    // 1. Gera arquivo MessageBrowser.sc para esta estação
                    $this->lrgsService->generateSearchCriteria(
                        $station->dcp_address,
                        $interval['start'],
                        $interval['end']
                    );

                    // 2. Busca mensagens
                    $filePath = $this->lrgsService->fetchMessages();
                    $fileContent = file_get_contents($filePath);

                    // 3. Divide pelo delimitador
                    $parts = explode($beforeDelimiter, $fileContent);

                    // Remove partes vazias
                    $messages = array_filter($parts, function($part) {
                        return !empty(trim($part));
                    });

                    // Remove linhas de status/finalizacao
                    $filteredMessages = array_filter($messages, function($line) {
                        $line = trim($line);
                        return !empty($line) &&
                            !str_contains($line, 'Normal termination') &&
                            !str_contains($line, 'Until time reached') &&
                            !str_contains($line, 'Missing message') &&        // NOVO
                            !str_contains($line, 'Wrong channel') &&          // NOVO
                            !str_contains($line, 'TESTE DE TRANSMISSAO');     // NOVO (opcional)
                    });
            

                    // 4. Processa e insere mensagens (agora retorna corrupted_headers)
                    $stats = $this->dcpReadingService->processAndInsertMessages(array_values($filteredMessages));

                    // 5. Completa o sync log com sucesso
                    $this->dcpSyncLogService->completeLog(
                        $syncLog->id,
                        $stats['total'],
                        $stats['inserted'],
                        $stats['corrupted'],
                        $stats['corrupted_headers']
                    );

                    // 6. Acumula estatísticas
                    $totalMessages += $stats['total'];
                    $totalInserted += $stats['inserted'];
                    $totalCorrupted += $stats['corrupted'];

                    $stationsStats[] = [
                        'station_id' => $station->id,
                        'name' => $station->name,
                        'address' => $station->dcp_address,
                        'messages' => $stats['total'],
                        'inserted' => $stats['inserted'],
                        'corrupted' => $stats['corrupted'],
                        'file' => $filePath,
                        'sync_log_id' => $syncLog->id
                    ];

                    Log::info('Estação DCP processada com sucesso', [
                        'station_id' => $station->id,
                        'sync_log_id' => $syncLog->id,
                        'messages' => $stats['total'],
                        'inserted' => $stats['inserted'],
                        'corrupted' => $stats['corrupted']
                    ]);

                } catch (\Exception $e) {
                    Log::error('Erro ao processar estação DCP', [
                        'station_id' => $station->id,
                        'station_name' => $station->name,
                        'sync_log_id' => $syncLog->id,
                        'error' => $e->getMessage()
                    ]);

                    // Marca sync log como 'failed'
                    $this->dcpSyncLogService->failLog($syncLog->id, $e->getMessage());

                    // Adiciona estação com erro nas estatísticas
                    $stationsStats[] = [
                        'station_id' => $station->id,
                        'name' => $station->name,
                        'address' => $station->dcp_address,
                        'messages' => 0,
                        'inserted' => 0,
                        'corrupted' => 0,
                        'error' => $e->getMessage(),
                        'sync_log_id' => $syncLog->id
                    ];

                    // Continua para próxima estação
                    continue;
                }
            }

            Log::info('Processamento de todas estações DCP concluído', [
                'total_stations' => count($stations),
                'total_messages' => $totalMessages,
                'total_inserted' => $totalInserted,
                'total_corrupted' => $totalCorrupted
            ]);

            // Após processar período corrente, tenta reprocessar logs pendentes/stuck
            $this->reprocessStuckJobs();

            

            return response()->json([
                'success' => true,
                'interval' => [
                    'start' => $interval['start']->toDateTimeString(),
                    'end' => $interval['end']->toDateTimeString()
                ],
                'total_stations' => count($stations),
                'total_messages' => $totalMessages,
                'total_inserted' => $totalInserted,
                'total_corrupted' => $totalCorrupted,
                'stations' => $stationsStats,
                'summary' => [
                    'processed' => $totalMessages,
                    'valid' => $totalInserted,
                    'invalid' => $totalCorrupted,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar mensagens DCP', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reprocess stuck or failed jobs (max 3 attempts)
     * Called at the end of normal processing
     */
    private function reprocessStuckJobs(): void
    {
        try {
            $pendingForRetry = (object) $this->dcpSyncLogService->findPendingForRetry();

            if ($pendingForRetry->isEmpty()) {
                Log::info('Nenhum job pendente/stuck para reprocessar');
                return;
            }

            Log::info('Iniciando reprocessamento de jobs pendentes/stuck', [
                'total_logs' => $pendingForRetry->count()
            ]);

            foreach ($pendingForRetry as $syncLog) {
                try {
                    // Verifica se ainda pode retentar
                    if (!$this->dcpSyncLogService->canRetry($syncLog->id)) {
                        Log::warning('Sync log atingiu limite de tentativas', [
                            'sync_log_id' => $syncLog->id,
                            'attempts' => $syncLog->attempts
                        ]);
                        continue;
                    }

                    Log::info('Reprocessando sync log', [
                        'sync_log_id' => $syncLog->id,
                        'station_id' => $syncLog->dcp_station_id,
                        'attempt' => $syncLog->attempts + 1
                    ]);

                    // Busca a estação
                    $station = $this->dcpStationService->getActiveStations()
                        ->firstWhere('id', $syncLog->dcp_station_id);

                    if (!$station) {
                        $this->dcpSyncLogService->failLog(
                            $syncLog->id,
                            'Estação não encontrada ou inativa'
                        );
                        continue;
                    }

                    // Soft delete readings antigos do período
                    $this->dcpReadingService->deleteByStationAndPeriod(
                        $syncLog->dcp_station_id,
                        $syncLog->start_time,
                        $syncLog->end_time
                    );

                    // Marca como 'running' e incrementa attempts
                    $this->dcpSyncLogService->startLog($syncLog->id);

                    // Gera search criteria
                    $this->lrgsService->generateSearchCriteria(
                        $station->dcp_address,
                        $syncLog->start_time,
                        $syncLog->end_time
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

                    Log::info('Sync log reprocessado com sucesso', [
                        'sync_log_id' => $syncLog->id,
                        'messages' => $stats['total'],
                        'inserted' => $stats['inserted'],
                        'corrupted' => $stats['corrupted']
                    ]);

                } catch (\Exception $e) {
                    Log::error('Erro ao reprocessar sync log', [
                        'sync_log_id' => $syncLog->id,
                        'error' => $e->getMessage()
                    ]);

                    $this->dcpSyncLogService->failLog($syncLog->id, $e->getMessage());
                }
            }

            Log::info('Reprocessamento concluído', [
                'total_processed' => $pendingForRetry->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erro durante reprocessamento de jobs stuck', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Manual reprocessing for specific station and time period
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
            $startTime = \Carbon\Carbon::parse($request->input('start_time'));
            $endTime = \Carbon\Carbon::parse($request->input('end_time'));

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
