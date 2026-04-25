<?php

namespace App\Repositories;

use App\Models\PocoSimahStation;
use App\Repositories\Interfaces\PocoSimahStationRepositoryInterface;
use Illuminate\Support\Collection;

class PocoSimahStationRepository implements PocoSimahStationRepositoryInterface
{
    public function all(): Collection
    {
        return PocoSimahStation::all();
    }

    public function find(int $id): ?PocoSimahStation
    {
        return PocoSimahStation::find($id);
    }

    public function findByCode(string $code): ?PocoSimahStation
    {
        return PocoSimahStation::where('station_code', $code)->first();
    }

    public function create(array $data): PocoSimahStation
    {
        return PocoSimahStation::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return PocoSimahStation::where('id', $id)->update($data) > 0;
    }

    public function delete(int $id): bool
    {
        $station = PocoSimahStation::find($id);
        return $station ? $station->delete() : false;
    }

    public function paginate(int $perPage = 20, array $filters = []): array
    {
        $query = PocoSimahStation::query();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['station_code'])) {
            $query->where('station_code', 'like', '%' . $filters['station_code'] . '%');
        }

        $total    = $query->count();
        $page     = $filters['page'] ?? 1;
        $lastPage = max(1, ceil($total / $perPage));

        $data = $query->orderBy('name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return [
            'data'         => $data,
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $total,
            'last_page'    => $lastPage,
        ];
    }

    public function getAllWithCoordinates(): Collection
    {
        return PocoSimahStation::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('ativa', true)
            ->get();
    }

}
