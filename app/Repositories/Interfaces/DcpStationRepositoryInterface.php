<?php
namespace App\Repositories\Interfaces;

use App\Models\DcpStation;
use Illuminate\Support\Collection;

interface DcpStationRepositoryInterface
{
    public function all(): Collection;
    
    public function find(int $id): ?DcpStation;
    
    public function findByDcpAddress(string $dcpAddress): ?DcpStation;
    
    public function create(array $data): DcpStation;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;

    public function getActive(): Collection;

    public function getAllWithCoordinates(): Collection;
}