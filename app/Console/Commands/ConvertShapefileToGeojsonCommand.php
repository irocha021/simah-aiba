<?php

namespace App\Console\Commands;

use App\Services\ShapefileToGeojsonService;
use Illuminate\Console\Command;

class ConvertShapefileToGeojsonCommand extends Command
{
    protected $signature = 'geojson:generate {zipPath} {--layer=}';
    protected $description = 'Converte um shapefile ZIP de pontos para GeoJSON';

    public function handle(ShapefileToGeojsonService $service)
    {
        $zipPath = $this->argument('zipPath');
        $layerName = $this->option('layer');

        if (!$layerName) {
            $layerName = pathinfo($zipPath, PATHINFO_FILENAME);
        }

        $fullPath = base_path($zipPath);

        if (!file_exists($fullPath)) {
            $this->error("Arquivo não encontrado: {$fullPath}");
            return 1;
        }

        $this->info("Convertendo shapefile para GeoJSON...");
        $this->info("Arquivo: {$zipPath}");
        $this->info("Layer: {$layerName}");

        $result = $service->convertToGeojson($fullPath, $layerName);

        if ($result['success']) {
            $this->info("GeoJSON gerado com sucesso!");
            $this->info("Caminho: {$result['geojson_path']}");
            $this->info("Features: {$result['feature_count']}");
            return 0;
        } else {
            $this->error("Erro: {$result['error']}");
            return 1;
        }
    }
}
