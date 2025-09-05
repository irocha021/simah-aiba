<?php
namespace App\Repositories\Interfaces;

use App\Models\DcpTransmissionRawData;

interface DcpTransmissionRawDataRepositoryInterface
{
    public function find(int $id): ?DcpTransmissionRawData;
    
    public function findByTransmissionId(int $transmissionId): ?DcpTransmissionRawData;
    
    public function create(array $data): DcpTransmissionRawData;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
}