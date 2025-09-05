<?php
// app/Repositories/DcpTransmissionRawDataRepository.php

namespace App\Repositories;

use App\Models\DcpTransmissionRawData;
use App\Repositories\Interfaces\DcpTransmissionRawDataRepositoryInterface;

class DcpTransmissionRawDataRepository implements DcpTransmissionRawDataRepositoryInterface
{
    public function find(int $id): ?DcpTransmissionRawData
    {
        return DcpTransmissionRawData::find($id);
    }
    
    public function findByTransmissionId(int $transmissionId): ?DcpTransmissionRawData
    {
        return DcpTransmissionRawData::where('transmission_id', $transmissionId)->first();
    }
    
    public function create(array $data): DcpTransmissionRawData
    {
        return DcpTransmissionRawData::create($data);
    }
    
    public function update(int $id, array $data): bool
    {
        return DcpTransmissionRawData::where('id', $id)->update($data);
    }
    
    public function delete(int $id): bool
    {
        return DcpTransmissionRawData::destroy($id);
    }
}