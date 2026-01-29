<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class ImportLayerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function __construct(protected array $layerData)
    {
    }

    public function handle(): void
    {
        if ($this->layerData['type'] === 'tile') {
            $pathZip = base_path($this->layerData['path_zip']);
            Artisan::call('tiles:generate', [
                'shapefile' => $pathZip,
                '--layer' => $this->layerData['slug'],
                '--zoom-min' => $this->layerData['min_zoom'],
                '--zoom-max' => $this->layerData['max_zoom']
            ]);
        } elseif ($this->layerData['type'] === 'geojson') {
            Artisan::call('geojson:generate', [
                'zipPath' => $this->layerData['path_zip'],
                '--layer' => $this->layerData['slug']
            ]);
        }
    }

}
