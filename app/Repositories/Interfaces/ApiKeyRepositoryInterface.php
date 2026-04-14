<?php

namespace App\Repositories\Interfaces;

use App\Models\ApiKey;

interface ApiKeyRepositoryInterface
{
    public function create(array $data): ApiKey;

    public function findByEmail(string $email): ?ApiKey;

    public function deactivate(int $id): void;
}
