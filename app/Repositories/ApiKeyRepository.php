<?php

namespace App\Repositories;

use App\Models\ApiKey;
use App\Repositories\Interfaces\ApiKeyRepositoryInterface;

class ApiKeyRepository implements ApiKeyRepositoryInterface
{
    public function create(array $data): ApiKey
    {
        return ApiKey::create($data);
    }

    public function findByEmail(string $email): ?ApiKey
    {
        return ApiKey::where('email', $email)
            ->where('is_active', true)
            ->first();
    }

    public function deactivate(int $id): void
    {
        ApiKey::where('id', $id)->update(['is_active' => false]);
    }
}
