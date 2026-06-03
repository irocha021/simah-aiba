<?php

namespace App\Jobs;

use App\Models\HwStationDrainageLayer;
use App\Services\ShapefileToTilesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateHwStationDrainageTilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 2;

    public function __construct(public int $stationCode) {}

    public function handle(ShapefileToTilesService $svc): void
    {
        $row = HwStationDrainageLayer::where('station_code', $this->stationCode)->firstOrFail();
        $row->update(['status' => 'processing', 'status_message' => null]);

        $layerName = "hw_station_drainage_{$this->stationCode}";
        $tilesDir = public_path("tiles/{$layerName}");

        // Limpa tiles antigos pra evitar órfãos de range anterior
        if (is_dir($tilesDir)) {
            exec('rm -rf ' . escapeshellarg($tilesDir));
        }

        // Paleta de 50 cores determinísticas derivadas do station_code.
        // Cada feature do shapefile cicla entre as 50 cores (replica o
        // "cor aleatória por feature" do QGIS). Alpha=255 (sem opacidade
        // no PNG — opacidade é controlada dinamicamente no frontend).
        $paletteSize = 50;
        $seed = crc32((string) $this->stationCode);
        $paletteLines = ['0 0 0 0 0']; // index 0 = transparente (fundo)
        for ($i = 1; $i <= $paletteSize; $i++) {
            $h = crc32("{$seed}_{$i}");
            $r = 80 + ((($h >> 16) & 0xFF) % 141);
            $g = 80 + ((($h >> 8) & 0xFF) % 141);
            $b = 80 + (($h & 0xFF) % 141);
            $paletteLines[] = "{$i} {$r} {$g} {$b} 255";
        }

        $config = [
            'color_per_feature'   => true,
            'palette_size'        => $paletteSize,
            'single_color'        => false,
            'borders_only'        => false,
            'draw_borders'        => true,
            'translucent_fill'    => true,
            'simplify_boundaries' => true,  // geometrias de drenagem são complexas — simplifica por zoom antes de desenhar borda
            'border_color'        => '0,0,0',
            'border_buffer'       => 0,
            'palette_text'        => implode("\n", $paletteLines),
            'sql_mapping_json'    => [],
            'field_name'          => null,
            'label_field'         => null,
        ];

        try {
            $result = $svc->convertToTilesWithConfig(
                base_path($row->zip_path),
                $layerName,
                $config,
                $row->min_zoom,
                $row->max_zoom
            );

            if (!($result['success'] ?? false)) {
                throw new \Exception($result['error'] ?? 'Falha desconhecida na geração de tiles');
            }

            $row->update([
                'status'              => 'ready',
                'url_pattern'         => "/tiles/{$layerName}/{z}/{x}/{y}.png",
                'bounds_json'         => $result['bounds'],
                'bounds_latlng_json'  => $this->mercatorBoundsToLatLng($result['bounds']),
                'tiles_generated_at'  => now(),
            ]);

            Log::info("Tiles de drenagem gerados para estação {$this->stationCode}");
        } catch (\Throwable $e) {
            $row->update([
                'status'         => 'failed',
                'status_message' => $e->getMessage(),
            ]);
            Log::error("Falha ao gerar tiles de drenagem {$this->stationCode}: " . $e->getMessage());
            throw $e;
        }
    }

    private function mercatorBoundsToLatLng(array $b): array
    {
        $toLng = fn($x) => $x / 20037508.34 * 180;
        $toLat = function ($y) {
            $lat = $y / 20037508.34 * 180;
            return 180 / M_PI * (2 * atan(exp($lat * M_PI / 180)) - M_PI / 2);
        };

        return [
            'south' => $toLat($b['ymin']),
            'west'  => $toLng($b['xmin']),
            'north' => $toLat($b['ymax']),
            'east'  => $toLng($b['xmax']),
        ];
    }
}
