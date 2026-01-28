<?php

namespace App\Services;

use App\Repositories\Interfaces\MapLayerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MapLayerService
{
    public function __construct(
        private MapLayerRepositoryInterface $mapLayerRepository
    ) {}

    public function getLayersForMap(): Collection
    {
        return $this->mapLayerRepository->getAllActiveWithLegends();
    }

    public function getLayerBySlug(string $slug): ?array
    {
        $layer = $this->mapLayerRepository->findBySlug($slug);
        
        if (!$layer) {
            return null;
        }

        return $layer->toArray();
    }

    public function updateLayerSettings(int $id, ?int $minZoom, ?int $maxZoom, ?float $opacity): bool
    {
        $data = [];
        
        if ($minZoom !== null) {
            $data['min_zoom'] = $minZoom;
        }
        if ($maxZoom !== null) {
            $data['max_zoom'] = $maxZoom;
        }
        if ($opacity !== null) {
            $data['opacity'] = $opacity;
        }
        
        if (empty($data)) {
            return false;
        }
        
        return $this->mapLayerRepository->updateLayer($id, $data);
    }

    public function getAllLayers(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->mapLayerRepository->getAll();
    }

}
