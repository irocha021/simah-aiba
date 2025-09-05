<?php
// app/Services/DcpTransmissionRawDataService.php

namespace App\Services;

use App\Repositories\Interfaces\DcpTransmissionRawDataRepositoryInterface;
use App\Models\DcpTransmissionRawData;

class DcpTransmissionRawDataService
{
    protected DcpTransmissionRawDataRepositoryInterface $rawDataRepository;

    public function __construct(DcpTransmissionRawDataRepositoryInterface $rawDataRepository)
    {
        $this->rawDataRepository = $rawDataRepository;
    }

    public function getRawData(int $id): ?DcpTransmissionRawData
    {
        return $this->rawDataRepository->find($id);
    }

    public function getRawDataByTransmissionId(int $transmissionId): ?DcpTransmissionRawData
    {
        return $this->rawDataRepository->findByTransmissionId($transmissionId);
    }

    public function createRawData(array $data): DcpTransmissionRawData
    {
        return $this->rawDataRepository->create($data);
    }

    public function updateRawData(int $id, array $data): bool
    {
        return $this->rawDataRepository->update($id, $data);
    }

    public function deleteRawData(int $id): bool
    {
        return $this->rawDataRepository->delete($id);
    }

    public function parseRawData(string $rawData): array
    {
        $lines = explode("\n", $rawData);
        $parsed = [];
        
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $parsed[trim($key)] = trim($value);
            }
        }
        
        return $parsed;
    }
}