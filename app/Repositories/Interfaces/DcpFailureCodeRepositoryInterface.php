<?php
// app/Repositories/Interfaces/DcpFailureCodeRepositoryInterface.php

namespace App\Repositories\Interfaces;

use App\Models\DcpFailureCode;
use Illuminate\Support\Collection;

interface DcpFailureCodeRepositoryInterface
{
    public function all(): Collection;
    
    public function find(int $id): ?DcpFailureCode;
    
    public function findByCode(string $code): ?DcpFailureCode;
    
    public function create(array $data): DcpFailureCode;
    
    public function update(int $id, array $data): bool;
}