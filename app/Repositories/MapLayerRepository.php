<?php

namespace App\Repositories;

use App\Models\MapLayer;
use App\Repositories\Interfaces\MapLayerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MapLayerRepository implements MapLayerRepositoryInterface
{
    public function getAllActive(): Collection
    {
        return MapLayer::where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }

    public function getAllActiveWithLegends(): Collection
    {
        return MapLayer::where('is_active', true)
            ->with('legends')
            ->orderBy('display_order')
            ->get();
    }

    public function findBySlug(string $slug): ?MapLayer
    {
        return MapLayer::where('slug', $slug)->with('legends')->first();
    }

    public function updateLayer(int $id, array $data): bool
    {
        return MapLayer::where('id', $id)->update($data) > 0;
    }

    public function getAll(): Collection
    {
        return MapLayer::orderBy('display_order')->get();
    }
}
