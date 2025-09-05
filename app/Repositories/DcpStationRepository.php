<?php
// app/Repositories/DcpStationRepository.php

namespace App\Repositories;

use App\Models\DcpStation;
use App\Repositories\Interfaces\DcpStationRepositoryInterface;
use Illuminate\Support\Collection;

class DcpStationRepository implements DcpStationRepositoryInterface
{
    public function all(): Collection
    {
        return DcpStation::all();
    }
    
    public function find(int $id): ?DcpStation
    {
        return DcpStation::find($id);
    }
    
    public function findByDcpAddress(string $dcpAddress): ?DcpStation
    {
        return DcpStation::where('dcp_address', $dcpAddress)->first();
    }
    
    public function create(array $data): DcpStation
    {
        return DcpStation::create($data);
    }
    
    public function update(int $id, array $data): bool
    {
        return DcpStation::where('id', $id)->update($data);
    }
    
    public function delete(int $id): bool
    {
        return DcpStation::destroy($id);
    }
    
    public function getActive(): Collection
    {
        return DcpStation::where('is_active', true)->get();
    }
}