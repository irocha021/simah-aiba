<?php

namespace App\Console\Commands;

use App\Services\ShapefileToTilesService;
use Illuminate\Console\Command;

class GenerateTilesCommand extends Command
{
    protected $signature = 'tiles:generate 
                            {shapefile : Caminho do arquivo ZIP do shapefile}
                            {--layer= : Nome da camada (ex: geologia_ana)}
                            {--zoom-min=5 : Nível de zoom mínimo}
                            {--zoom-max=10 : Nível de zoom máximo}';

    protected $description = 'Converte um shapefile ZIP para tiles PNG no formato XYZ';

    public function handle(ShapefileToTilesService $service)
    {
        $zipPath = $this->argument('shapefile');
        $layerName = $this->option('layer');
        $zoomMin = (int) $this->option('zoom-min');
        $zoomMax = (int) $this->option('zoom-max');

        // Validar arquivo
        if (!file_exists($zipPath)) {
            $this->error("Arquivo não encontrado: {$zipPath}");
            return 1;
        }

        // Se não informou o nome da camada, extrair do nome do arquivo
        if (!$layerName) {
            $layerName = pathinfo($zipPath, PATHINFO_FILENAME);
            $this->info("Nome da camada não informado. Usando: {$layerName}");
        }

        $this->info("Iniciando conversão de shapefile para tiles...");
        $this->info("Arquivo: {$zipPath}");
        $this->info("Camada: {$layerName}");
        $this->info("Zoom: {$zoomMin} até {$zoomMax}");
        $this->newLine();

        // Executar conversão
        $this->info("Este processo pode demorar vários minutos...");
        
        $result = $service->convertToTiles($zipPath, $layerName, $zoomMin, $zoomMax);

        if ($result['success']) {
            $this->newLine();
            $this->info("✓ Tiles gerados com sucesso!");
            $this->info("Camada: {$result['layer']}");
            $this->info("Localização: {$result['tiles_path']}");
            $this->info("Zoom range: {$result['zoom_range']}");
            
            if (isset($result['bounds'])) {
                $this->info("Bounds:");
                $this->line("  xmin: {$result['bounds']['xmin']}");
                $this->line("  ymin: {$result['bounds']['ymin']}");
                $this->line("  xmax: {$result['bounds']['xmax']}");
                $this->line("  ymax: {$result['bounds']['ymax']}");
            }
            
            $this->newLine();
            $this->info("Para usar no Leaflet, adicione:");
            $this->line("L.tileLayer('/tiles/{$result['layer']}/{z}/{x}/{y}.png', {");
            $this->line("    maxZoom: {$zoomMax},");
            $this->line("    minZoom: {$zoomMin}");
            $this->line("}).addTo(map);");
            
            return 0;
        } else {
            $this->error("✗ Erro ao gerar tiles:");
            $this->error($result['error']);
            return 1;
        }
    }
}
