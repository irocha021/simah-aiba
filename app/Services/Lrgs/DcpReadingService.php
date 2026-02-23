<?php

namespace App\Services\Lrgs;

use App\Repositories\Interfaces\DcpReadingRepositoryInterface;
use Illuminate\Support\Facades\Log;

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
        $validReadings = [];
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

            $validReadings[] = $result;
        }

        $insertedCount = 0;

        if (!empty($validReadings)) {
            $this->dcpReadingRepository->bulkInsert($validReadings);
            $insertedCount = count($validReadings);

            // Após inserir, verifica e preenche NULLs do registro anterior
            foreach ($validReadings as $reading) {
                $previous = $this->dcpReadingRepository->findPreviousReading(
                    $reading['address'],
                    (string) $reading['reading_datetime']
                );

                if (!$previous) {
                    continue;
                }

                $this->dcpReadingRepository->updateNullReadings($previous->id, [
                    'water_level_60min' => is_null($previous->water_level_60min) ? $reading['water_level_120min'] : null,
                    'water_level_45min' => is_null($previous->water_level_45min) ? $reading['water_level_105min'] : null,
                    'water_level_30min' => is_null($previous->water_level_30min) ? $reading['water_level_90min'] : null,
                    'water_level_15min' => is_null($previous->water_level_15min) ? $reading['water_level_75min'] : null,
                    'rain_60min'        => is_null($previous->rain_60min) ? $reading['rain_120min'] : null,
                    'rain_45min'        => is_null($previous->rain_45min) ? $reading['rain_105min'] : null,
                    'rain_30min'        => is_null($previous->rain_30min) ? $reading['rain_90min'] : null,
                    'rain_15min'        => is_null($previous->rain_15min) ? $reading['rain_75min'] : null,
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
