<?php

namespace App\Services;

use App\Repositories\Interfaces\PocoSimahStationRepositoryInterface;

class PocoSimahStationService
{
    protected $repository;

    public function __construct(PocoSimahStationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function paginate(int $perPage = 20, array $filters = []): array
    {
        return $this->repository->paginate($perPage, $filters);
    }

    public function find(int $id)
    {
        return $this->repository->find($id);
    }

    public function findByCode(string $code)
    {
        return $this->repository->findByCode($code);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
