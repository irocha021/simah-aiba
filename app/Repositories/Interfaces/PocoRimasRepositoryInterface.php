<?php

namespace App\Repositories\Interfaces;

use App\Models\PocoRimas;
use Illuminate\Support\Collection;

interface PocoRimasRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?PocoRimas;

    public function findByIdPonto(int $idPonto): ?PocoRimas;

    public function create(array $data): PocoRimas;

    public function createBatch(array $records): int;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function truncate(): bool;

    public function softDeleteAll(): bool;

    public function paginate(int $perPage = 15, int $page = 1): array;

    public function getAllWithCoordinates(): Collection;

    public function getReadingsByIdPonto(string $idPonto, int $limit = 50);

    public function getReadingsByIdPontoAndDateRange(int $idPonto, ?string $dateFrom, ?string $dateTo): Collection;

}
