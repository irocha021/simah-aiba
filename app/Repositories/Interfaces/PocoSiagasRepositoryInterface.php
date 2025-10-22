<?php

namespace App\Repositories\Interfaces;

use App\Models\PocoSiagas;
use Illuminate\Support\Collection;

interface PocoSiagasRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?PocoSiagas;

    public function findByPonto(int $ponto): ?PocoSiagas;

    public function create(array $data): PocoSiagas;

    public function createBatch(array $records): int;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function truncate(): bool;

    public function softDeleteAll(): bool;

    public function paginate(int $perPage = 15, int $page = 1): array;
}
