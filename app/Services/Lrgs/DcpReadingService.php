<?php

namespace App\Services\Lrgs;

use App\Repositories\Interfaces\DcpReadingRepositoryInterface;

class DcpReadingService
{
    private DcpReadingRepositoryInterface $dcpReadingRepository;
    private DcpMessageProcessor $messageProcessor;

    public function __construct(
        DcpReadingRepositoryInterface $dcpReadingRepository,
        DcpMessageProcessor $messageProcessor
    ) {
        $this->dcpReadingRepository = $dcpReadingRepository;
        $this->messageProcessor = $messageProcessor;
    }

    /**
     * Processa e insere mensagens DCP em lote
     *
     * @param array $messages
     * @return array Estatísticas do processamento + corrupted_headers array
     */
    public function processAndInsertMessages(array $messages): array
    {
        $validReadings    = [];
        $corruptedHeaders = [];

        foreach ($messages as $message) {
            $result = $this->messageProcessor->processMessage($message);

            if ($result === null) {
                $parts = explode(";", $message);
                if (!empty($parts[0])) {
                    $corruptedHeaders[] = substr(trim($parts[0]), 0, 200);
                }
                continue;
            }

            // Captura o flow record imediatamente após o processamento
            $pendingFlow = $this->messageProcessor->pendingFlowRecord;
            $this->messageProcessor->pendingFlowRecord = [];

            $validReadings[] = [
                'data' => $result,
                'flow' => $pendingFlow,
            ];
        }

        $insertedCount = 0;

        if (!empty($validReadings)) {
            foreach ($validReadings as $item) {
                $inserted = $this->dcpReadingRepository->create($item['data']);
                $insertedCount++;

                // Grava rastreamento de flow se houver
                if (!empty($item['flow'])) {
                    \App\Models\DcpReadingFlow::create(array_merge(
                        $item['flow'],
                        ['dcp_reading_id' => $inserted->id]
                    ));
                }

                // Preenche NULLs do registro anterior
                $previous = $this->dcpReadingRepository->findPreviousReading(
                    $item['data']['address'],
                    (string) $item['data']['reading_datetime']
                );

                if (!$previous) {
                    continue;
                }

                $this->dcpReadingRepository->updateNullReadings($previous->id, [
                    'water_level_60min' => is_null($previous->water_level_60min) ? $item['data']['water_level_120min'] : null,
                    'water_level_45min' => is_null($previous->water_level_45min) ? $item['data']['water_level_105min'] : null,
                    'water_level_30min' => is_null($previous->water_level_30min) ? $item['data']['water_level_90min'] : null,
                    'water_level_15min' => is_null($previous->water_level_15min) ? $item['data']['water_level_75min'] : null,
                    'rain_60min'        => is_null($previous->rain_60min) ? $item['data']['rain_120min'] : null,
                    'rain_45min'        => is_null($previous->rain_45min) ? $item['data']['rain_105min'] : null,
                    'rain_30min'        => is_null($previous->rain_30min) ? $item['data']['rain_90min'] : null,
                    'rain_15min'        => is_null($previous->rain_15min) ? $item['data']['rain_75min'] : null,
                ]);
            }
        }


        return [
            'total' => count($messages),
            'inserted' => $insertedCount,
            'corrupted' => count($corruptedHeaders),
            'corrupted_headers' => $corruptedHeaders,
        ];
    }


    /**
     * Busca leituras por estação
     *
     * @param int $stationId
     * @return \Illuminate\Support\Collection
     */
    public function getReadingsByStation(int $stationId)
    {
        return $this->dcpReadingRepository->findByStationId($stationId);
    }

    /**
     * Busca leituras por range de data
     *
     * @param int $stationId
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Support\Collection
     */
    public function getReadingsByDateRange(int $stationId, string $startDate, string $endDate)
    {
        return $this->dcpReadingRepository->findByDateRange($stationId, $startDate, $endDate);
    }

    /**
     * Busca leituras por endereço DCP
     *
     * @param string $address
     * @return \Illuminate\Support\Collection
     */
    public function getReadingsByAddress(string $address, int $limit = 50)
    {
        return $this->dcpReadingRepository->findByAddress($address, $limit);
    }

    public function getReadingsByAddressAndDateRange(string $address, string $dateFrom, string $dateTo)
    {
        return $this->dcpReadingRepository->findByAddressAndDateRange($address, $dateFrom, $dateTo);
    }

    public function cursorReadingsByAddressAndDateRange(string $address, ?string $dateFrom, ?string $dateTo): \Generator
    {
        return $this->dcpReadingRepository->cursorByAddressAndDateRange($address, $dateFrom, $dateTo);
    }


    /**
     * Soft delete readings for a specific station and time period
     * Used before reprocessing to remove old data
     *
     * @param int $stationId
     * @param \Carbon\Carbon $startTime
     * @param \Carbon\Carbon $endTime
     * @return int Number of records soft deleted
     */
    public function deleteByStationAndPeriod(int $stationId, $startTime, $endTime): int
    {
        return $this->dcpReadingRepository->softDeleteByStationAndPeriod($stationId, $startTime, $endTime);
    }

}
