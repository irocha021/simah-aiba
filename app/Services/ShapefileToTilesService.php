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

        // Remove limite de tamanho de objetos GeoJSON para o GDAL/OGR,
        // necessário para shapefiles de drenagem com geometrias muito complexas.
        putenv('OGR_GEOJSON_MAX_OBJ_SIZE=0');
    }

    public function convertToTiles(string $zipPath, string $layerName, int $zoomMin = 5, int $zoomMax = 10): array
    {
        $config = $this->mapLayerService->getLayerBySlug($layerName);
        return $this->convertToTilesWithConfig($zipPath, $layerName, $config, $zoomMin, $zoomMax);
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
    public function convertToTilesWithConfig(string $zipPath, string $layerName, array $config, int $zoomMin = 5, int $zoomMax = 10): array
    {   
    try {

        // Configuração injetada (banco ou caller direto)
        $this->layerConfig = $config;

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

            $tilesDir = $this->tilesBaseDir . '/' . $layerName;
            $bordersOnly = $this->layerConfig['borders_only'] ?? false;

            if ($bordersOnly) {
                // Modo borders_only: gera um raster por zoom com buffer escalado por zoom.
                // A borda é mais fina nos zooms baixos e engrossa progressivamente nos altos,
                // imitando o comportamento visual de mapas como GeoBahia.
                $borderPixelsMax = $this->layerConfig['border_buffer'] ?? 2; // pixels no zoom mais alto
                $borderPixelsMin = 0.3;                                       // pixels no zoom mais baixo

                for ($z = $zoomMin; $z <= $zoomMax; $z++) {
                    // Interpolação linear: 0 no zoomMin, 1 no zoomMax
                    $factor = ($zoomMax > $zoomMin)
                        ? ($z - $zoomMin) / ($zoomMax - $zoomMin)
                        : 1.0;
                    $borderPixels = $borderPixelsMin + ($borderPixelsMax - $borderPixelsMin) * $factor;

                    $resolution = $this->metersPerPixel($z);
                    $bufferMeters = $borderPixels * $resolution;

                    Log::info("Zoom {$z}: pixels={$borderPixels}, resolução={$resolution} m/pixel, buffer={$bufferMeters} m");

                    $rasterFile = $extractDir . '/' . $layerName . "_z{$z}.tif";
                    $this->createBordersOnlyRaster($geojsonFile, $rasterFile, $resolution, $bufferMeters, $this->layerConfig['simplify_boundaries'] ?? false);

                    // Roda gdal2tiles só para este zoom; os tiles caem em $tilesDir/{z}/...
                    $this->generateTiles($rasterFile, $tilesDir, $z, $z);

                    @unlink($rasterFile);
                }
            } elseif ($this->layerConfig['translucent_fill'] ?? false) {
                // Modo translucent_fill: preenchimento pálido translúcido + borda
                // colorida nítida. Usa o MESMO loop por-zoom do borders_only
                // (1 raster por zoom, espessura de borda escalada por zoom) para
                // a borda não ficar enorme nos zooms baixos.
                // Espessura da linha em PIXELS na tela, constante em todos os
                // zooms (cálculo dinâmico: o buffer em metros é recalculado por
                // zoom via metersPerPixel, então a linha tem sempre a mesma
                // grossura visual). border_buffer do banco = espessura em px.
                $borderThicknessPx = $this->layerConfig['border_buffer'] ?? 1.5;

                // Piso de espessura: garante linha visível em qualquer zoom.
                // 0.75px: com -at a linha não some mesmo fina (ALL_TOUCHED
                // pinta o pixel tocado), então dá pra ir bem mais fino que
                // 1.5 sem o problema de sumir no zoom baixo.
                $minThicknessPx = 0.5;

                // TETO do buffer em metros. No zoom baixo metros/pixel é enorme
                // (4891 m/px no z5) => buffer dinâmico explodia para ~7 km e o
                // ST_Buffer de 7 km em 339 feições matava o processo por falta
                // de memória (exit 137). Acima de ~500 m de espessura real não
                // há ganho visual — só risco de OOM. Com -at a linha aparece
                // mesmo com buffer pequeno no zoom baixo.
                $maxBufferMeters = 500;

                Log::info("[translucent_fill] === INÍCIO geração '{$layerName}' zooms {$zoomMin}-{$zoomMax} ===");

                for ($z = $zoomMin; $z <= $zoomMax; $z++) {
                    $resolution = $this->metersPerPixel($z);

                    // ST_Buffer engorda a linha para OS DOIS lados => raio do
                    // buffer = METADE da espessura. Cálculo dinâmico por zoom,
                    // limitado pelo teto para não estourar memória no zoom baixo.
                    $effectiveThicknessPx = max($borderThicknessPx, $minThicknessPx);
                    $bufferMeters = min(($effectiveThicknessPx / 2.0) * $resolution, $maxBufferMeters);

                    Log::info("[translucent_fill] Zoom {$z}: espessura={$effectiveThicknessPx}px, resolução={$resolution} m/pixel, buffer={$bufferMeters} m (teto={$maxBufferMeters})");

                    $rasterFile = $extractDir . '/' . $layerName . "_z{$z}.tif";
                    $this->createFilledTranslucentRasterForZoom($geojsonFile, $rasterFile, $resolution, $bufferMeters, $this->layerConfig['simplify_boundaries'] ?? false);

                    $this->generateTiles($rasterFile, $tilesDir, $z, $z);

                    @unlink($rasterFile);

                    Log::info("[translucent_fill] Zoom {$z} CONCLUÍDO ({$z}/{$zoomMax})");
                }

                    Log::info("[translucent_fill] ===== FINALIZADO '{$layerName}': todos os zooms {$zoomMin}-{$zoomMax} gerados com sucesso =====");
            } elseif ($this->layerConfig['line_style'] ?? false) {
                // Camada de LINHA (rios/hidrografia): rasteriza o traço direto,
                // sem ST_Boundary/ST_Buffer (que são pra polígono). 1 raster
                // por zoom pra a linha não engrossar no zoom afastado.
                Log::info("[line_style] === INÍCIO geração '{$layerName}' zooms {$zoomMin}-{$zoomMax} ===");

                for ($z = $zoomMin; $z <= $zoomMax; $z++) {
                    $resolution = $this->metersPerPixel($z);
                    Log::info("[line_style] Zoom {$z}: resolução={$resolution} m/pixel");

                    $rasterFile = $extractDir . '/' . $layerName . "_z{$z}.tif";
                    $this->createLineRaster($geojsonFile, $rasterFile, $resolution);

                    $this->generateTiles($rasterFile, $tilesDir, $z, $z);

                    @unlink($rasterFile);

                    Log::info("[line_style] Zoom {$z} CONCLUÍDO ({$z}/{$zoomMax})");
                }

                Log::info("[line_style] ===== FINALIZADO '{$layerName}': todos os zooms {$zoomMin}-{$zoomMax} gerados com sucesso =====");
            } else {

                
                // Caminho antigo (camadas com preenchimento): inalterado.
                Log::info("Convertendo GeoJSON para raster");
                $rasterFile = $extractDir . '/' . $layerName . '.tif';
                $this->geojsonToRaster($geojsonFile, $rasterFile);

                Log::info("Gerando tiles PNG (zoom {$zoomMin}-{$zoomMax})");
                $this->generateTiles($rasterFile, $tilesDir, $zoomMin, $zoomMax);
            }

            // 6.5. Gerar labels.geojson se a camada tem label_field configurado
            $labelField = $this->layerConfig['label_field'] ?? null;
            if ($labelField) {
                Log::info("[labels] === INÍCIO geração labels.geojson com campo '{$labelField}' ===");
                $labelsFile = $tilesDir . '/labels.geojson';
                $this->extractLabels($shpFile, $labelsFile, $labelField);

                // Conta quantos labels saíram (útil pra ver se o dedup
                // funcionou — milhares = sem agrupamento; centenas = ok).
                $count = 0;
                if (file_exists($labelsFile)) {
                    $json = json_decode(file_get_contents($labelsFile), true);
                    $count = isset($json['features']) ? count($json['features']) : 0;
                }
                Log::info("[labels] ===== FINALIZADO labels.geojson: {$count} labels gerados =====");
            }

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
        $isSingleColor     = $this->layerConfig['single_color'] ?? false;
        $isColorPerFeature = $this->layerConfig['color_per_feature'] ?? false;
        $paletteSize       = $this->layerConfig['palette_size'] ?? 50;

        $tableName = basename($shpFile, '.shp');

        if ($isColorPerFeature) {
            // Cada feature recebe um color_id ciclando entre 1 e palette_size.
            // OGR_FID é único por feature e começa em 0 no SQLite dialect do ogr2ogr.
            $sql = "SELECT *, (rowid % {$paletteSize}) + 1 AS color_id FROM \"{$tableName}\"";
        } elseif ($isSingleColor) {
            // Camada de cor única: todos features recebem color_id = 1
            $sql = "SELECT *, 1 AS color_id FROM \"{$tableName}\"";
        } else {
            // Gerar SQL CASE baseado na configuração ou usar padrão
            $field = $this->layerConfig['field_name'] ?? 'gid';
            $sqlMapping = $this->layerConfig['sql_mapping_json'] ?? [];

            if (empty($sqlMapping)) {
                // Fallback: usar SQL simples sem CASE
                $sql = "SELECT * FROM \"{$tableName}\"";
            } else {
                // Gerar SQL CASE dinamicamente
                $caseStatements = [];
                foreach ($sqlMapping as $value => $colorId) {
                    $caseStatements[] = "WHEN {$field}='{$value}' THEN {$colorId}";
                }
                $caseSQL = implode(' ', $caseStatements);
                $sql = "SELECT *, CASE {$caseSQL} ELSE 0 END AS color_id FROM \"{$tableName}\"";
            }
        }

        $command = sprintf(
            'OGR_GEOJSON_MAX_OBJ_SIZE=0 SHAPE_RESTORE_SHX=YES ogr2ogr -f GeoJSON -t_srs EPSG:3857 -simplify 0 -dialect SQLite -sql %s %s %s 2>&1',
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
        // O caminho borders_only agora é tratado direto no convertToTiles() (loop por zoom).
        // Aqui só chega o modo de preenchimento.
        $this->createFilledRaster($geojsonFile, $rasterFile);
    }


    private function createBordersOnlyRaster(
        string $geojsonFile,
        string $rasterFile,
        float $resolutionMeters,
        float $bufferMeters,
        bool $simplifyBoundaries = false
    ): void
    {
        // 1. Extrair boundaries como LINESTRING com buffer para engrossar
        $boundariesFile = str_replace('.geojson', '_boundaries.geojson', $geojsonFile);

        if ($simplifyBoundaries) {
            $simplifyTolerance = $resolutionMeters / 2.0;
            $geometryExpr = sprintf('ST_SimplifyPreserveTopology(geometry, %f)', $simplifyTolerance);
        } else {
            $geometryExpr = 'geometry';
        }

        $extractCommand = sprintf(
            'OGR_GEOJSON_MAX_OBJ_SIZE=0 ogr2ogr -f GeoJSON -dialect SQLite -sql "SELECT ST_Buffer(ST_Boundary(%s), %d) as geometry FROM \\"SELECT\\"" %s %s 2>&1',
            $geometryExpr,
            $bufferMeters,
            escapeshellarg($boundariesFile),
            escapeshellarg($geojsonFile)
        );

        exec($extractCommand, $extractOutput, $extractCode);

        if ($extractCode !== 0 || !file_exists($boundariesFile)) {
            throw new \Exception("Erro ao extrair boundaries: " . implode("\n", $extractOutput));
        }

        // 2. Criar raster RGBA transparente vazio
        $bounds = $this->getBounds($geojsonFile);
        $width = ceil(($bounds['xmax'] - $bounds['xmin']) / $resolutionMeters);
        $height = ceil(($bounds['ymax'] - $bounds['ymin']) / $resolutionMeters);


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
        // border_color vem como string "R,G,B" do banco — converte para array de ints.
        $borderColorRaw = $this->layerConfig['border_color'] ?? '0,0,0';
        $borderColor = is_string($borderColorRaw)
            ? array_map('intval', explode(',', $borderColorRaw))
            : $borderColorRaw;

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

        /**
     * Cria um raster RGBA (4 bandas) para um zoom específico com:
     *  - INTERIOR preenchido com cor pálida e ALPHA BAIXO (translúcido) e
     *  - BORDA desenhada por cima com cor sólida e ALPHA 255 (nítida).
     *
     * Usado pelo modo translucent_fill. Modelado em createBordersOnlyRaster():
     * a translucidez fica ASSADA no PNG, sem depender da opacidade do Leaflet.
     *
     * NÃO usa applyColorPalette()/gdaldem de propósito — color-relief ignora a
     * coluna de alpha da paleta (gera alpha binário 0/255). Aqui o palette_text
     * é lido só como fonte do RGBA do preenchimento (linha "1 R G B A").
     */
    private function createFilledTranslucentRasterForZoom(
        string $geojsonFile,
        string $rasterFile,
        float $resolutionMeters,
        float $bufferMeters,
        bool $simplifyBoundaries = false
    ): void
    {
        // 1. Cor do preenchimento: lê a linha "1 R G B A" do palette_text.
        $fill = [250, 248, 210, 90]; // fallback: amarelo pálido translúcido
        $paletteText = $this->layerConfig['palette_text'] ?? '';
        // palette_text no banco vem com "\n" LITERAL (2 chars), não quebra
        // real. Normaliza ambos os casos antes de separar as linhas.
        $paletteText = str_replace('\n', "\n", $paletteText);
        foreach (preg_split('/\r\n|\r|\n/', $paletteText) as $line) {
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) >= 5 && $parts[0] === '1') {
                $fill = [(int)$parts[1], (int)$parts[2], (int)$parts[3], (int)$parts[4]];
                break;
            }
        }

        // 2. Cor da borda: border_color vem como "R,G,B" do banco.
        $borderColorRaw = $this->layerConfig['border_color'] ?? '0,0,0';
        $borderColor = is_string($borderColorRaw)
            ? array_map('intval', explode(',', $borderColorRaw))
            : $borderColorRaw;

        // 3. Criar raster RGBA transparente vazio na resolução DESTE zoom.
        $bounds = $this->getBounds($geojsonFile);
        $width = ceil(($bounds['xmax'] - $bounds['xmin']) / $resolutionMeters);
        $height = ceil(($bounds['ymax'] - $bounds['ymin']) / $resolutionMeters);

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

        // 4. Preencher o INTERIOR (polígono inteiro) com cor pálida + alpha baixo.
        //    SEM -at: -at sangraria o preenchimento para fora do polígono.
        $fillCommand = sprintf(
            'gdal_rasterize -b 1 -b 2 -b 3 -b 4 -burn %d -burn %d -burn %d -burn %d %s %s 2>&1',
            $fill[0],
            $fill[1],
            $fill[2],
            $fill[3],
            escapeshellarg($geojsonFile),
            escapeshellarg($rasterFile)
        );

        exec($fillCommand, $fillOutput, $fillCode);

        if ($fillCode !== 0) {
            throw new \Exception("Erro ao preencher interior: " . implode("\n", $fillOutput));
        }

        // 5. Extrair a borda como buffer da boundary (espessura escalada por zoom).
        $boundariesFile = str_replace('.geojson', '_boundaries.geojson', $geojsonFile);

        if ($simplifyBoundaries) {
            $simplifyTolerance = $resolutionMeters / 2.0;
            $geometryExpr = sprintf('ST_SimplifyPreserveTopology(geometry, %f)', $simplifyTolerance);
        } else {
            $geometryExpr = 'geometry';
        }

        $extractCommand = sprintf(
            'OGR_GEOJSON_MAX_OBJ_SIZE=0 ogr2ogr -f GeoJSON -dialect SQLite -sql "SELECT ST_Buffer(ST_Boundary(%s), %f) as geometry FROM \\"SELECT\\"" %s %s 2>&1',
            $geometryExpr,
            $bufferMeters,
            escapeshellarg($boundariesFile),
            escapeshellarg($geojsonFile)
        );

        exec($extractCommand, $extractOutput, $extractCode);

        if ($extractCode !== 0 || !file_exists($boundariesFile)) {
            throw new \Exception("Erro ao extrair boundaries: " . implode("\n", $extractOutput));
        }

        // 6. Desenhar a BORDA por cima do fill, alpha 255 (nítida). COM -at:
        //    sem -at a linha some no zoom baixo (buffer com teto vira sub-pixel
        //    e o GDAL não acha centro de pixel para pintar). -at pinta todo
        //    pixel tocado => aparece em TODOS os zooms 5-12. Combinado com o
        //    teto de buffer (não estoura memória) é a combinação estável. Vem
        //    DEPOIS do fill: a borda opaca sobrescreve o fill nas arestas.
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


    /**
     * Rasteriza uma geometria de LINHA (rios/hidrografia) para um zoom.
     *
     * Diferente dos métodos de polígono (createBordersOnlyRaster /
     * createFilledTranslucentRasterForZoom), NÃO usa ST_Boundary nem
     * ST_Buffer — a linha é desenhada DIRETO do GeoJSON. Sem buffer não há
     * o gargalo de memória/lentidão que os polígonos tinham.
     *
     * -at (ALL_TOUCHED) é obrigatório: sem ele a linha fina vira sub-pixel
     * no zoom afastado e o GDAL não a desenha (some). A cor vem de
     * border_color ("R,G,B" do banco), reaproveitando a coluna existente.
     */
    private function createLineRaster(
        string $geojsonFile,
        string $rasterFile,
        float $resolutionMeters
    ): void
    {
        // 1. Extent + tamanho do raster na resolução DESTE zoom.
        $bounds = $this->getBounds($geojsonFile);
        $width  = ceil(($bounds['xmax'] - $bounds['xmin']) / $resolutionMeters);
        $height = ceil(($bounds['ymax'] - $bounds['ymin']) / $resolutionMeters);

        // 2. Cor da linha: border_color vem como "R,G,B" do banco.
        $lineColorRaw = $this->layerConfig['border_color'] ?? '0,100,200';
        $lineColor = is_string($lineColorRaw)
            ? array_map('intval', explode(',', $lineColorRaw))
            : $lineColorRaw;

        // 3. Criar raster RGBA transparente vazio.
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

        // 4. Desenhar a LINHA direto do GeoJSON (sem ST_Boundary/ST_Buffer).
        //    -at garante que a linha fina não suma no zoom afastado.
        $lineCommand = sprintf(
            'gdal_rasterize -b 1 -b 2 -b 3 -b 4 -burn %d -burn %d -burn %d -burn 255 -at %s %s 2>&1',
            $lineColor[0],
            $lineColor[1],
            $lineColor[2],
            escapeshellarg($geojsonFile),
            escapeshellarg($rasterFile)
        );

        exec($lineCommand, $lineOutput, $lineCode);

        if ($lineCode !== 0) {
            throw new \Exception("Erro ao desenhar linha: " . implode("\n", $lineOutput));
        }
    }

    /**
     * Extrai centroides + label de cada feature do shapefile e salva como GeoJSON.
     *
     * Usa ST_PointOnSurface (não ST_Centroid) para garantir que o ponto fique
     * dentro do polígono mesmo em formas irregulares. Reprojeta para EPSG:4326
     * (lat/lng), formato esperado pelo Leaflet.
     */
    private function extractLabels(string $shpFile, string $outputFile, string $fieldName): void
    {
        // Apaga arquivo anterior se existir (ogr2ogr não sobrescreve por padrão)
        @unlink($outputFile);

        $shpLayer = basename($shpFile, '.shp');

        $isLine = $this->layerConfig['line_style'] ?? false;

        if ($isLine) {
            // Camada de LINHA (rios): o shapefile quebra cada rio em MUITOS
            // segmentos, todos com o mesmo NOME. Sem agrupar, gera 1 label
            // por segmento => milhares de nomes repetidos sobrepostos (trava
            // o mapa). Solução cartográfica padrão: fundir os segmentos por
            // nome (ST_LineMerge+ST_Collect) e gerar 1 ponto por rio, no
            // ponto sobre a geometria fundida. Ignora nomes vazios/nulos.
            $sql = sprintf(
                'SELECT ST_PointOnSurface(ST_LineMerge(ST_Collect(geometry))) AS geometry, %1$s AS name '
                . 'FROM "%2$s" WHERE %1$s IS NOT NULL AND %1$s <> \'\' GROUP BY %1$s',
                $fieldName,
                $shpLayer
            );
        } else {
            // Camada de polígono (ex.: Municípios SEI): 1 feição = 1 label.
            // Comportamento ORIGINAL inalterado.
            $sql = sprintf(
                'SELECT ST_PointOnSurface(geometry) AS geometry, %s AS name FROM "%s"',
                $fieldName,
                $shpLayer
            );
        }


        $command = sprintf(
            'OGR_GEOJSON_MAX_OBJ_SIZE=0 SHAPE_RESTORE_SHX=YES ogr2ogr -f GeoJSON -t_srs EPSG:4326 -dialect SQLite -sql %s %s %s 2>&1',
            escapeshellarg($sql),
            escapeshellarg($outputFile),
            escapeshellarg($shpFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            Log::warning("Falha ao extrair labels: " . implode("\n", $output));
        }
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
            'OGR_GEOJSON_MAX_OBJ_SIZE=0 ogr2ogr -f GeoJSON -dialect SQLite -sql "SELECT ST_Buffer(ST_Boundary(geometry), '.$borderBuffer .') as geometry FROM \\"SELECT\\"" %s %s 2>&1',
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
        
        // Comando ORIGINAL (mantido para referência — não usar com exec()
        // porque exec() só devolve a saída no final, sem progresso):
        //   'gdal2tiles.py -z %d-%d -w none --processes=4 %s %s 2>&1'
        //   exec($command, $output, $returnCode);
        //   if ($returnCode !== 0) {
        //       throw new \Exception("Erro ao gerar tiles: " . implode("\n", $output));
        //   }

        // Sem o pipe `| while ...; exit ${PIPESTATUS}`: popen() roda via
        // /bin/sh (não bash), onde ${PIPESTATUS} não existe -> o `exit`
        // falhava com código 2 mesmo o gdal2tiles terminando OK (0..100),
        // o que abortava a geração no meio dos zooms. O popen()+fgets()
        // abaixo já lê linha a linha em tempo real (o while do shell era
        // redundante), e o pclose() retorna o código REAL do gdal2tiles.
        $command = sprintf(
            'gdal2tiles.py -z %d-%d -w none --processes=4 %s %s 2>&1',
            $zoomMin,
            $zoomMax,
            escapeshellarg($rasterFile),
            escapeshellarg($tilesDir)
        );

        // popen lê a saída do gdal2tiles linha a linha ENQUANTO ele roda,
        // então a barra de progresso nativa do GDAL vai pro log em tempo
        // real (exec() só devolveria tudo no final). O 2>&1 já está no
        // $command acima, não duplicar aqui.
        $handle = popen($command, 'r');
        if ($handle === false) {
            throw new \Exception("Erro ao iniciar gdal2tiles");
        }

        $lastLine = '';
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line !== '') {
                $lastLine = $line;
                Log::info("[gdal2tiles z{$zoomMin}] {$line}");
            }
        }

        $returnCode = pclose($handle);

        if ($returnCode !== 0) {
            throw new \Exception("Erro ao gerar tiles (código {$returnCode}): {$lastLine}");
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

    /**
     * Resolução em metros/pixel de um tile Web Mercator (EPSG:3857) num dado zoom.
     *
     * Fórmula equatorial: 156543.03 m/pixel no zoom 0, dividido por 2^z.
     * Para a latitude da Bahia o erro é ~3% — aceitável.
     *
     * Usada para: (a) definir a resolução do raster gerado para cada zoom,
     * (b) converter "espessura desejada em pixels" em metros para o ST_Buffer.
     */
    private function metersPerPixel(int $zoom): float
    {
        return 156543.03 / pow(2, $zoom);
    }

}
