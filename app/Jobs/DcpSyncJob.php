<?php
// app/Jobs/DcpSyncJob.php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\DcpStationService;
use App\Services\DcpSyncLogService;
use App\Services\DcpMonitor\DcpMonitorService;
use App\Services\DcpStationTransmissionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; 

class DcpSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected DcpStationService $stationService;
    protected DcpSyncLogService $syncLogService;
    protected DcpMonitorService $monitorService;
    protected DcpStationTransmissionService $transmissionService;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(
        DcpStationService $stationService,
        DcpSyncLogService $syncLogService,
        DcpMonitorService $monitorService,
        DcpStationTransmissionService $transmissionService
    ): void
    {
        $this->stationService = $stationService;
        $this->syncLogService = $syncLogService;
        $this->monitorService = $monitorService;
        $this->transmissionService = $transmissionService;

        Log::info('DcpSyncJob iniciado');

        // Busca todas as estações ativas
        $activeStations = $this->stationService->getActiveStations();

        Log::info("Encontradas {$activeStations->count()} estações ativas");

        // Processa cada estação
        foreach ($activeStations as $station) {
            $this->processStation($station);
        }

        Log::info('DcpSyncJob finalizado');
    }

    /**
     * Processa uma estação específica
     */
    protected function processStation($station): void
    {
        Log::info("Processando estação: {$station->dcp_address}");

        // Inicia log de sincronização
        $syncLog = $this->syncLogService->startSync(
            $station->id,
            Carbon::now()->format('m/d/Y'),
            Carbon::now()->format('m/d/Y')
        );

        try {
            // Busca dados da estação com raw data
            $dcpData = $this->monitorService->fetchCompleteData(
                $station->dcp_address,
                true // incluir raw data
            );

            $transmissionsFound = count($dcpData['transmissions'] ?? []);
            
            // Conta quantos têm raw data
            $rawDataFetched = 0;
            foreach ($dcpData['transmissions'] ?? [] as $transmission) {
                if (isset($transmission['raw_data']) && $transmission['raw_data']) {
                    $rawDataFetched++;
                }
            }

            // Atualizar o progresso com os totais encontrados
            $this->syncLogService->updateProgress(
                $syncLog->id,
                $transmissionsFound,
                $rawDataFetched
            );

            // Debug - ver estrutura dos dados retornados
            Log::info("Dados recebidos para {$station->dcp_address}", [
                'transmissions_found' => $transmissionsFound,
                'raw_data_fetched' => $rawDataFetched,
                'metadata' => $dcpData['metadata'] ?? null
            ]);

            // Processa e salva as transmissões
            $savedCount = $this->saveTransmissions($station, $dcpData['transmissions'] ?? []);

            // Atualiza log de sucesso com TODOS os parâmetros
            $this->syncLogService->completeSync(
                $syncLog->id,
                $transmissionsFound,                    // transmissions_found
                $savedCount['transmissions_saved'],     // transmissions_saved
                $rawDataFetched,                        // raw_data_fetched
                $savedCount['raw_data_saved']           // raw_data_saved
            );

            Log::info("Estação {$station->dcp_address} sincronizada com sucesso", [
                'transmissions_found' => $transmissionsFound,
                'transmissions_saved' => $savedCount['transmissions_saved'],
                'raw_data_fetched' => $rawDataFetched,
                'raw_data_saved' => $savedCount['raw_data_saved']
            ]);

        } catch (\Exception $e) {
            // Registra falha
            $this->syncLogService->failSync($syncLog->id, $e->getMessage());
            
            Log::error("Erro ao processar estação {$station->dcp_address}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Salva transmissões no banco
     */
    protected function saveTransmissions($station, array $transmissions): array
    {
        $transmissionsSaved = 0;
        $rawDataSaved = 0;
        $transmissionsUpdated = 0;

        foreach ($transmissions as $transmission) {
            try {
                // Prepara dados da transmissão
                $transmissionData = $this->prepareTransmissionData($station, $transmission);
                
                // Extrai raw data
                $rawData = $transmission['raw_data'] ?? null;

                // Chaves únicas para identificar a transmissão
                $uniqueKeys = [
                    'station_id' => $station->id,
                    'date' => $transmissionData['date'],
                    'window_start' => $transmissionData['window_start']
                ];

                // UpdateOrCreate - atualiza se existir, cria se não existir
                $existingTransmission = DB::table('dcp_station_transmissions')
                    ->where($uniqueKeys)
                    ->first();

                if ($existingTransmission) {
                    // Atualiza transmissão existente
                    DB::table('dcp_station_transmissions')
                        ->where('id', $existingTransmission->id)
                        ->update(array_merge($transmissionData, ['updated_at' => now()]));
                    
                    $transmissionId = $existingTransmission->id;
                    $transmissionsUpdated++;
                    
                    // Atualiza ou cria raw data
                    if ($rawData) {
                        $existingRawData = DB::table('dcp_transmission_raw_data')
                            ->where('transmission_id', $transmissionId)
                            ->exists();
                        
                        if ($existingRawData) {
                            DB::table('dcp_transmission_raw_data')
                                ->where('transmission_id', $transmissionId)
                                ->update([
                                    'raw_data' => $rawData,
                                    'updated_at' => now()
                                ]);
                        } else {
                            DB::table('dcp_transmission_raw_data')->insert([
                                'transmission_id' => $transmissionId,
                                'raw_data' => $rawData,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $rawDataSaved++;
                        }
                    }
                } else {
                    // Cria nova transmissão
                    $transmissionId = DB::table('dcp_station_transmissions')->insertGetId(
                        array_merge($transmissionData, ['created_at' => now(), 'updated_at' => now()])
                    );
                    
                    $transmissionsSaved++;
                    
                    // Cria raw data se existir
                    if ($rawData && $transmissionId) {
                        DB::table('dcp_transmission_raw_data')->insert([
                            'transmission_id' => $transmissionId,
                            'raw_data' => $rawData,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $rawDataSaved++;
                    }
                }

            } catch (\Exception $e) {
                Log::warning("Erro ao salvar transmissão", [
                    'station' => $station->dcp_address,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Resumo de sincronização para {$station->dcp_address}", [
            'novas' => $transmissionsSaved,
            'atualizadas' => $transmissionsUpdated,
            'raw_data_salvos' => $rawDataSaved
        ]);

        return [
            'transmissions_saved' => $transmissionsSaved + $transmissionsUpdated,
            'raw_data_saved' => $rawDataSaved
        ];
    }

    /**
     * Prepara dados da transmissão para salvar
     */
    protected function prepareTransmissionData($station, array $transmission): array
    {
        // Converte data do formato mm/dd/yyyy para yyyy-mm-dd
        $date = Carbon::createFromFormat('m/d/Y', $transmission['date'])->format('Y-m-d');

        return [
            'station_id' => $station->id,
            'date' => $date,
            'transmit_start' => $transmission['transmit_start']['time'] ?? '--:--:--',
            'transmit_end' => $transmission['transmit_end']['time'] ?? '--:--:--',
            'window_start' => $transmission['window_start'] ?? null,
            'window_end' => $transmission['window_end'] ?? null,
            'failure_code' => $transmission['failure_code'] ?? null,
            'is_successful' => $transmission['is_successful'] ?? false,
            'signal_strength' => $transmission['signal_strength'] ?? null,
            'message_length' => $transmission['message_length'] ?? null,
            'frequency_offset' => $transmission['frequency_offset'] ?? null,
            'modulation_index' => $transmission['modulation_index'] ?? null,
            'drgs_code' => $transmission['drgs_code'] ?? null,
            'battery_voltage' => $transmission['battery_voltage'] ?? null,
            'message_filename' => $this->extractMessageFilename($transmission),
            'message_link' => $transmission['message_link'] ?? null,
        ];
    }

    /**
     * Extrai filename da mensagem
     */
    protected function extractMessageFilename(array $transmission): ?string
    {
        if (isset($transmission['message_link'])) {
            preg_match('/msgfilename=([^&]+)/', $transmission['message_link'], $matches);
            return $matches[1] ?? null;
        }
        return null;
    }
}