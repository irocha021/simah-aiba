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
                // Extract just the header part (first 200 chars) for logging
                $parts = explode(";", $message);
                if (!empty($parts[0])) {
                    $corruptedHeaders[] = substr(trim($parts[0]), 0, 200);
                }
                continue;
            }

            $validReadings[] = $result;
        }

        // Inserção em lote
        $insertedCount = 0;
        if (!empty($validReadings)) {
            $this->dcpReadingRepository->bulkInsert($validReadings);
            $insertedCount = count($validReadings);
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
    public function getReadingsByAddress(string $address)
    {
        return $this->dcpReadingRepository->findByAddress($address);
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
