<?php

namespace App\Repositories\Interfaces;

use App\Models\MapLayer;
use Illuminate\Database\Eloquent\Collection;

interface MapLayerRepositoryInterface
{
    public function getAllActive(): Collection;

    public function getAllActiveWithLegends(): Collection;

    public function findBySlug(string $slug): ?MapLayer;

    public function updateLayer(int $id, array $data): bool;

    public function getAll(): Collection;
}
