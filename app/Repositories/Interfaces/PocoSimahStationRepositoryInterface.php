<?php

namespace App\Repositories\Interfaces;

use App\Models\PocoSimahStation;
use Illuminate\Support\Collection;

interface PocoSimahStationRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?PocoSimahStation;
    public function findByCode(string $code): ?PocoSimahStation;
    public function create(array $data): PocoSimahStation;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function paginate(int $perPage = 20, array $filters = []): array;
    public function getAllWithCoordinates(): Collection;
}
