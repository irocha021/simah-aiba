<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use ZipArchive;

class ShapefileToGeojsonService
{
    private string $tempDir;
    private string $geojsonBaseDir;

    public function __construct()
    {
        $this->tempDir = storage_path('app/temp');
        $this->geojsonBaseDir = public_path('geojson');

        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }

        if (!is_dir($this->geojsonBaseDir)) {
            mkdir($this->geojsonBaseDir, 0755, true);
        }
    }

    /**
     * Converte um shapefile ZIP para GeoJSON
     */
    public function convertToGeojson(string $zipPath, string $layerName): array
    {
        $extractDir = null;

        try {

            // 1. Extrair ZIP
            Log::info("Extraindo shapefile ZIP: {$zipPath}");
            $extractDir = $this->extractZip($zipPath);

            // 2. Encontrar arquivo .shp
            $shpFile = $this->findShapeFile($extractDir);
            if (!$shpFile) {
                throw new \Exception("Arquivo .shp não encontrado no ZIP");
            }

            // 3. Converter para GeoJSON (reprojetar para WGS84 EPSG:4326)
            Log::info("Convertendo shapefile para GeoJSON");
            $geojsonFile = $this->geojsonBaseDir . '/' . $layerName . '.geojson';
            $this->shapefileToGeoJSON($shpFile, $geojsonFile);

            // 4. Contar features
            $featureCount = $this->countFeatures($geojsonFile);

            return [
                'success' => true,
                'layer' => $layerName,
                'geojson_path' => "public/geojson/{$layerName}.geojson",
                'feature_count' => $featureCount,
            ];

        } catch (\Exception $e) {
            Log::error("Erro ao converter shapefile: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        } finally {
            // A limpeza precisa rodar também quando a conversão falha; caso
            // contrário o diretório extraído fica órfão em storage/app/temp.
            if ($extractDir !== null) {
                $this->cleanupTemp($extractDir);
            }
        }
    }

    private function extractZip(string $zipPath): string
    {
        $zip = new ZipArchive();
        $extractDir = $this->tempDir . '/' . uniqid('shp_');

        if ($zip->open($zipPath) !== true) {
            throw new \Exception("Não foi possível abrir o arquivo ZIP");
        }

        mkdir($extractDir, 0755, true);

        // Daqui em diante o diretório já existe no disco. Se a extração
        // falhar, ele precisa ser removido aqui: o finally do chamador só
        // recebe o caminho quando este método retorna.
        if (!$zip->extractTo($extractDir)) {
            $zip->close();
            $this->cleanupTemp($extractDir);
            throw new \Exception("Falha ao extrair o ZIP: {$zipPath}");
        }

        $zip->close();

        return $extractDir;
    }

    private function findShapeFile(string $dir): ?string
    {
        $files = glob($dir . '/*.shp');
        return $files[0] ?? null;
    }

   
    private function shapefileToGeoJSON(string $shpFile, string $geojsonFile): void
    {
        // Converter para GeoJSON em WGS84 (EPSG:4326) para uso no Leaflet
        $command = sprintf(
            'SHAPE_RESTORE_SHX=YES ogr2ogr -f GeoJSON -t_srs EPSG:4326 %s %s 2>&1',
            escapeshellarg($geojsonFile),
            escapeshellarg($shpFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Erro ao converter shapefile: " . implode("\n", $output));
        }
    }

    private function countFeatures(string $geojsonFile): int
    {
        $content = file_get_contents($geojsonFile);
        $data = json_decode($content, true);
        return count($data['features'] ?? []);
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
