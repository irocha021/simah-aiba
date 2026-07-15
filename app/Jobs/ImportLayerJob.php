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
        // Remove restos de imports que morreram sem chance de limpar (timeout
        // do worker, OOM). O container de cron não tem o código do projeto
        // montado, então não há schedule:run para pendurar isso; o ponto de
        // entrada dos imports é o lugar natural, já que é ele quem gera esse
        // lixo. Só apaga diretório com mais de 24h — nunca o do import atual.
        Artisan::call('temp:prune');

        if ($this->layerData['type'] === 'tile') {
            $pathZip = base_path($this->layerData['path_zip']);
            $exitCode = Artisan::call('tiles:generate', [
                'shapefile' => $pathZip,
                '--layer' => $this->layerData['slug'],
                '--zoom-min' => $this->layerData['min_zoom'],
                '--zoom-max' => $this->layerData['max_zoom']
            ]);
        } elseif ($this->layerData['type'] === 'geojson') {
            $exitCode = Artisan::call('geojson:generate', [
                'zipPath' => $this->layerData['path_zip'],
                '--layer' => $this->layerData['slug']
            ]);
        } else {
            throw new \RuntimeException(
                "Tipo de camada não suportado: " . ($this->layerData['type'] ?? 'null')
            );
        }

        // Sem checar o exit code, uma falha do comando (ex.: mkdir/permission
        // negada, gdal2tiles com erro) encerrava o job "com sucesso": não ia
        // para failed_jobs nem disparava retry, escondendo o erro. Agora um
        // código != 0 vira exceção -> o job falha, registra em failed_jobs e
        // aciona as tentativas (--tries) do worker.
        if ($exitCode !== 0) {
            throw new \RuntimeException(sprintf(
                "Falha ao importar camada '%s' (comando retornou %d):\n%s",
                $this->layerData['slug'] ?? '?',
                $exitCode,
                Artisan::output()
            ));
        }
    }

}
