<?php
// app/Repositories/DcpFailureCodeRepository.php

namespace App\Repositories;

use App\Models\DcpFailureCode;
use App\Repositories\Interfaces\DcpFailureCodeRepositoryInterface;
use Illuminate\Support\Collection;

class DcpFailureCodeRepository implements DcpFailureCodeRepositoryInterface
{
    public function all(): Collection
    {
        return DcpFailureCode::all();
    }
    
    public function find(int $id): ?DcpFailureCode
    {
        return DcpFailureCode::find($id);
    }
    
    public function findByCode(string $code): ?DcpFailureCode
    {
        return DcpFailureCode::where('code', $code)->first();
    }
    
    public function create(array $data): DcpFailureCode
    {
        return DcpFailureCode::create($data);
    }
    
    public function update(int $id, array $data): bool
    {
        return DcpFailureCode::where('id', $id)->update($data);
    }
}