<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use ZipArchive;

class ShapefileToTilesService
{
    private string $tempDir;
    private string $tilesBaseDir;
    private array $layerConfig;
    private MapLayerService $mapLayerService;
    
    public function __construct(
        MapLayerService $mapLayerService
    )
    {
        $this->tempDir = storage_path('app/temp');
        $this->tilesBaseDir = public_path('tiles');

        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }

        $this->mapLayerService = $mapLayerService;
    }

    /**
     * Converte um shapefile ZIP para tiles PNG
     *
     * @param string $zipPath Caminho do arquivo ZIP
     * @param string $layerName Nome da camada (usado no path dos tiles)
     * @param int $zoomMin Zoom mínimo
     * @param int $zoomMax Zoom máximo
     * @return array Resultado da conversão
     */
    public function convertToTiles(string $zipPath, string $layerName, int $zoomMin = 5, int $zoomMax = 10): array
    {   
        try {

            // Carregar configuração da camada
            $this->layerConfig = $this->mapLayerService->getLayerBySlug($layerName);

            // 1. Extrair ZIP
            Log::info("Extraindo shapefile ZIP: {$zipPath}");
            $extractDir = $this->extractZip($zipPath);

            // 2. Encontrar arquivo .shp
            $shpFile = $this->findShapeFile($extractDir);
            if (!$shpFile) {
                throw new \Exception("Arquivo .shp não encontrado no ZIP");
            }

            // 3. Converter para GeoJSON (reprojetar para Web Mercator EPSG:3857)
            Log::info("Convertendo shapefile para GeoJSON");
            $geojsonFile = $extractDir . '/' . $layerName . '.geojson';
            $this->shapefileToGeoJSON($shpFile, $geojsonFile);

            // DEBUG: Copiar GeoJSON para public para análise
            // $debugGeojson = public_path("debug_{$layerName}.geojson");
            // copy($geojsonFile, $debugGeojson);
            // Log::info("GeoJSON copiado para: {$debugGeojson}");

            // 4. Obter bounds do shapefile
            $bounds = $this->getBounds($geojsonFile);
            
            // 5. Converter GeoJSON para Raster (GeoTIFF)
            Log::info("Convertendo GeoJSON para raster");
            $rasterFile = $extractDir . '/' . $layerName . '.tif';
            $this->geojsonToRaster($geojsonFile, $rasterFile);
            
            // 6. Gerar tiles PNG
            Log::info("Gerando tiles PNG (zoom {$zoomMin}-{$zoomMax})");
            $tilesDir = $this->tilesBaseDir . '/' . $layerName;
            $this->generateTiles($rasterFile, $tilesDir, $zoomMin, $zoomMax);

            // DEBUG: Copiar TIF final para public para análise
            // $debugTif = public_path("debug_{$layerName}.tif");
            // copy($rasterFile, $debugTif);
            // Log::info("TIF copiado para: {$debugTif}");

            // 7. Limpar arquivos temporários
            $this->cleanupTemp($extractDir);
            
            return [
                'success' => true,
                'layer' => $layerName,
                'tiles_path' => "public/tiles/{$layerName}",
                'zoom_range' => "{$zoomMin}-{$zoomMax}",
                'bounds' => $bounds,
            ];
            
        } catch (\Exception $e) {
            Log::error("Erro ao converter shapefile: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function extractZip(string $zipPath): string
    {
        $zip = new ZipArchive();
        $extractDir = $this->tempDir . '/' . uniqid('shp_');
        
        if ($zip->open($zipPath) === true) {
            mkdir($extractDir, 0755, true);
            $zip->extractTo($extractDir);
            $zip->close();
            return $extractDir;
        }
        
        throw new \Exception("Não foi possível abrir o arquivo ZIP");
    }

    private function findShapeFile(string $dir): ?string
    {
        $files = glob($dir . '/*.shp');
        return $files[0] ?? null;
    }

    

    private function shapefileToGeoJSON(string $shpFile, string $geojsonFile): void
    {
        $isSingleColor = $this->layerConfig['single_color'] ?? false;


        if ($isSingleColor) {
            // Camada de cor única: todos features recebem color_id = 1
            $sql = "SELECT *, 1 AS color_id FROM \"" . basename($shpFile, '.shp') . "\"";
        } else {
            // Gerar SQL CASE baseado na configuração ou usar padrão
            $field = $this->layerConfig['field_name'] ?? 'gid';
            $sqlMapping = $this->layerConfig['sql_mapping_json'] ?? [];

            if (empty($sqlMapping)) {
                // Fallback: usar SQL simples sem CASE
                $sql = "SELECT * FROM \"" . basename($shpFile, '.shp') . "\"";
            } else {
                // Gerar SQL CASE dinamicamente
                $caseStatements = [];
                foreach ($sqlMapping as $value => $colorId) {
                    $caseStatements[] = "WHEN {$field}='{$value}' THEN {$colorId}";
                }
                $caseSQL = implode(' ', $caseStatements);
                $sql = "SELECT *, CASE {$caseSQL} ELSE 0 END AS color_id FROM \"" . basename($shpFile, '.shp') . "\"";
            }
        }

        $command = sprintf(
            'SHAPE_RESTORE_SHX=YES ogr2ogr -f GeoJSON -t_srs EPSG:3857 -simplify 0 -dialect SQLite -sql %s %s %s 2>&1',
            escapeshellarg($sql),
            escapeshellarg($geojsonFile),
            escapeshellarg($shpFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Erro ao converter shapefile: " . implode("\n", $output));
        }
    }


    private function getBounds(string $geojsonFile): array
    {
        $command = sprintf(
            'ogrinfo -al -so %s 2>&1 | grep "Extent:"',
            escapeshellarg($geojsonFile)
        );

        exec($command, $output);

        // Extent: (xmin, ymin) - (xmax, ymax)
        if (isset($output[0]) && preg_match('/\(([-\d.]+), ([-\d.]+)\) - \(([-\d.]+), ([-\d.]+)\)/', $output[0], $matches)) {
            return [
                'xmin' => (float)$matches[1],
                'ymin' => (float)$matches[2],
                'xmax' => (float)$matches[3],
                'ymax' => (float)$matches[4],
            ];
        }

        // Bounds padrão (Brasil)
        return [
            'xmin' => -73.9872354804,
            'ymin' => -33.7683777809,
            'xmax' => -34.7299934555,
            'ymax' => 5.24448639569,
        ];
    }

    private function geojsonToRaster(string $geojsonFile, string $rasterFile): void
{
    $bordersOnly = $this->layerConfig['borders_only'] ?? false;

    if ($bordersOnly) {
        // Modo apenas bordas: extrair boundaries e desenhar linhas
        $this->createBordersOnlyRaster($geojsonFile, $rasterFile);
    } else {
        // Modo normal: preenchimento + bordas opcionais
        $this->createFilledRaster($geojsonFile, $rasterFile);
    }
}

    private function createBordersOnlyRaster(string $geojsonFile, string $rasterFile): void
    {
        // 1. Extrair boundaries como LINESTRING com buffer para engrossar
        $boundariesFile = str_replace('.geojson', '_boundaries.geojson', $geojsonFile);

        // Usar buffer para engrossar as linhas (padrão 500m, configurável)
        $bufferSize = $this->layerConfig['border_buffer'] ?? 500;

        $extractCommand = sprintf(
            'ogr2ogr -f GeoJSON -dialect SQLite -sql "SELECT ST_Buffer(ST_Boundary(geometry), %d) as geometry FROM \\"SELECT\\"" %s %s 2>&1',
            $bufferSize,
            escapeshellarg($boundariesFile),
            escapeshellarg($geojsonFile)
        );

        exec($extractCommand, $extractOutput, $extractCode);

        if ($extractCode !== 0 || !file_exists($boundariesFile)) {
            throw new \Exception("Erro ao extrair boundaries: " . implode("\n", $extractOutput));
        }

        // 2. Criar raster RGBA transparente vazio
        $bounds = $this->getBounds($geojsonFile);
        $width = ceil(($bounds['xmax'] - $bounds['xmin']) / 50);
        $height = ceil(($bounds['ymax'] - $bounds['ymin']) / 50);

        $createCommand = sprintf(
            'gdal_create -of GTiff -outsize %d %d -a_srs EPSG:3857 -a_ullr %f %f %f %f -burn 0 -ot Byte -co COMPRESS=LZW -bands 4 %s 2>&1',
            $width,
            $height,
            $bounds['xmin'],
            $bounds['ymax'],
            $bounds['xmax'],
            $bounds['ymin'],
            escapeshellarg($rasterFile)
        );

        exec($createCommand, $createOutput, $createCode);

        if ($createCode !== 0) {
            throw new \Exception("Erro ao criar raster vazio: " . implode("\n", $createOutput));
        }

        // 3. Desenhar apenas as linhas (bordas)
        $borderColor = $this->layerConfig['border_color'] ?? [0, 0, 0];

        $borderCommand = sprintf(
            'gdal_rasterize -b 1 -b 2 -b 3 -b 4 -burn %d -burn %d -burn %d -burn 255 -at %s %s 2>&1',
            $borderColor[0],
            $borderColor[1],
            $borderColor[2],
            escapeshellarg($boundariesFile),
            escapeshellarg($rasterFile)
        );

        exec($borderCommand, $borderOutput, $borderCode);

        if ($borderCode !== 0) {
            throw new \Exception("Erro ao desenhar bordas: " . implode("\n", $borderOutput));
        }

        @unlink($boundariesFile);
    }

    private function createFilledRaster(string $geojsonFile, string $rasterFile): void
    {
        // 1. Criar raster com preenchimento (color_id)
        $command = sprintf(
            'gdal_rasterize -a color_id -tr 50 50 -tap -at -a_nodata 0 -ot Byte -of GTiff -co COMPRESS=LZW %s %s 2>&1',
            escapeshellarg($geojsonFile),
            escapeshellarg($rasterFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Erro ao gerar raster: " . implode("\n", $output));
        }

        // 2. Aplicar paleta de cores (converte para RGB)
        $this->applyColorPalette($rasterFile);

        // 3. Extrair bordas como LINESTRING (se configurado)
        $drawBorders = $this->layerConfig['draw_borders'] ?? false;
        $borderBuffer = $this->layerConfig['border_buffer'] ?? 1500;

        if (!$drawBorders) {
            return;
        }

        $boundariesFile = str_replace('.geojson', '_boundaries.geojson', $geojsonFile);

        // O layer name no GeoJSON gerado por SQL query é sempre "SELECT" (palavra reservada, precisa de aspas)
        $extractCommand = sprintf(
            'ogr2ogr -f GeoJSON -dialect SQLite -sql "SELECT ST_Buffer(ST_Boundary(geometry), '.$borderBuffer .') as geometry FROM \\"SELECT\\"" %s %s 2>&1',
            escapeshellarg($boundariesFile),
            escapeshellarg($geojsonFile)
        );

        Log::info("Executando extração de bordas: " . $extractCommand);
        exec($extractCommand, $extractOutput, $extractCode);

        Log::info("Código de retorno da extração: $extractCode");
        Log::info("Output da extração: " . implode("\n", $extractOutput));
        Log::info("Arquivo boundaries existe? " . (file_exists($boundariesFile) ? 'SIM' : 'NÃO'));

        if ($extractCode === 0 && file_exists($boundariesFile)) {
            Log::info("Entrando no IF para desenhar bordas");

            // Desenhar bordas PRETAS no arquivo RGB
            $borderCommand = sprintf(
                'gdal_rasterize -b 1 -b 2 -b 3 -burn 0 -burn 0 -burn 0 -at %s %s 2>&1',
                escapeshellarg($boundariesFile),
                escapeshellarg($rasterFile)
            );

            Log::info("Comando de bordas: " . $borderCommand);
            exec($borderCommand, $borderOutput, $borderCode);

            Log::info("Código de retorno das bordas: $borderCode");
            Log::info("Output das bordas: " . implode("\n", $borderOutput));

            if ($borderCode !== 0) {
                Log::warning("Bordas não adicionadas: " . implode("\n", $borderOutput));
            } else {
                Log::info("Bordas adicionadas com sucesso!");
            }

            @unlink($boundariesFile);
        } else {
            Log::error("NÃO entrou no IF! ExtractCode=$extractCode, Arquivo existe=" . (file_exists($boundariesFile) ? 'SIM' : 'NÃO'));
        }
    }


    // private function geojsonToRaster(string $geojsonFile, string $rasterFile): void
    // {
    //     // Rasterizar usando o campo color_id (criado na conversão do shapefile)
    //     // Sem -te: GDAL calcula extent automaticamente do GeoJSON (preserva TODA geometria)
    //     // -tr 50 50: resolução de 50 metros por pixel em Web Mercator (EPSG:3857)
    //     // -at: ALL_TOUCHED - todos pixels tocados pela geometria (preserva detalhes)
    //     // -tap: alinha pixels ao extent (evita cortes)
    //     $command = sprintf(
    //         'gdal_rasterize -a color_id -tr 50 50 -tap -at -a_nodata 0 -ot Byte -of GTiff -co COMPRESS=LZW %s %s 2>&1',
    //         escapeshellarg($geojsonFile),
    //         escapeshellarg($rasterFile)
    //     );

    //     exec($command, $output, $returnCode);

    //     if ($returnCode !== 0) {
    //         throw new \Exception("Erro ao gerar raster: " . implode("\n", $output));
    //     }

    //     // Aplicar paleta de cores
    //     $this->applyColorPalette($rasterFile);
    // }

    private function applyColorPalette(string $rasterFile): void
    {
        $paletteFile = dirname($rasterFile) . '/palette.txt';

        // Usar paleta da configuração ou gerar padrão
        if (isset($this->layerConfig['palette_text'])) {
            $palette = $this->layerConfig['palette_text'];
        } else {
            // Paleta padrão simples
            $palette = <<<PALETTE
0 0 0 0 0
1 255 0 0 255
2 0 255 0 255
3 0 0 255 255
4 255 255 0 255
nv 0 0 0 0
PALETTE;
        }

        file_put_contents($paletteFile, $palette);

        // Criar arquivo colorido com canal alfa para transparência
        $coloredFile = str_replace('.tif', '_colored.tif', $rasterFile);

        $command = sprintf(
            'gdaldem color-relief %s %s %s -alpha -of GTiff -co COMPRESS=LZW 2>&1',
            escapeshellarg($rasterFile),
            escapeshellarg($paletteFile),
            escapeshellarg($coloredFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($coloredFile)) {
            // Substituir arquivo original pelo colorido
            rename($coloredFile, $rasterFile);
        }

        // Limpar arquivo de paleta
        @unlink($paletteFile);
    }

    private function generateTiles(string $rasterFile, string $tilesDir, int $zoomMin, int $zoomMax): void
    {
        // Criar diretório de tiles se não existir
        if (!is_dir($tilesDir)) {
            mkdir($tilesDir, 0755, true);
        }
        
        $command = sprintf(
            'gdal2tiles.py -z %d-%d -w none --processes=4 %s %s 2>&1',
            $zoomMin,
            $zoomMax,
            escapeshellarg($rasterFile),
            escapeshellarg($tilesDir)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("Erro ao gerar tiles: " . implode("\n", $output));
        }
    }

    private function cleanupTemp(string $dir): void
    {
        if (is_dir($dir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }

            rmdir($dir);
        }
    }
}
