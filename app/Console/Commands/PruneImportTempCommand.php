<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneImportTempCommand extends Command
{
    protected $signature = 'temp:prune
                            {--hours=24 : Idade mínima, em horas, para um diretório ser removido}
                            {--dry-run : Apenas lista o que seria removido, sem apagar nada}';

    protected $description = 'Remove diretórios de trabalho de import (storage/app/temp/shp_*) deixados por execuções interrompidas';

    public function handle(): int
    {
        $tempDir = storage_path('app/temp');

        if (! is_dir($tempDir)) {
            $this->info("Nada a fazer: {$tempDir} não existe.");

            return self::SUCCESS;
        }

        $hours = max(1, (int) $this->option('hours'));
        $dryRun = (bool) $this->option('dry-run');

        // Os serviços de conversão limpam o próprio diretório num finally, o
        // que cobre sucesso e exceção. O que não cobrem é o processo morto sem
        // aviso — timeout do worker (SIGKILL), OOM ou erro fatal do PHP. Este
        // comando é a rede de segurança para esses casos.
        //
        // O corte por idade evita apagar o diretório de um import em curso: o
        // timeout do job é de 1h, então o padrão de 24h dá margem de sobra.
        $cutoff = time() - ($hours * 3600);

        $removidos = 0;
        $ignorados = 0;
        $bytesLiberados = 0;

        foreach (glob($tempDir . '/shp_*', GLOB_ONLYDIR) ?: [] as $dir) {
            if (filemtime($dir) > $cutoff) {
                $ignorados++;
                continue;
            }

            $tamanho = $this->tamanhoDoDiretorio($dir);

            if ($dryRun) {
                $this->line(sprintf('[dry-run] %s — %s', basename($dir), $this->formatarBytes($tamanho)));
                $removidos++;
                $bytesLiberados += $tamanho;
                continue;
            }

            if ($this->removerDiretorio($dir)) {
                $this->line(sprintf('Removido %s — %s', basename($dir), $this->formatarBytes($tamanho)));
                $removidos++;
                $bytesLiberados += $tamanho;
            } else {
                $this->warn("Não foi possível remover {$dir}");
            }
        }

        $resumo = sprintf(
            '%s %d diretório(s), %s liberados. %d ignorado(s) por terem menos de %dh.',
            $dryRun ? 'Seriam removidos' : 'Removidos',
            $removidos,
            $this->formatarBytes($bytesLiberados),
            $ignorados,
            $hours
        );

        $this->info($resumo);

        if ($removidos > 0 && ! $dryRun) {
            Log::info("[temp:prune] {$resumo}");
        }

        return self::SUCCESS;
    }

    private function tamanhoDoDiretorio(string $dir): int
    {
        $total = 0;

        foreach ($this->percorrer($dir) as $item) {
            if ($item->isFile()) {
                $total += $item->getSize();
            }
        }

        return $total;
    }

    private function removerDiretorio(string $dir): bool
    {
        foreach ($this->percorrer($dir) as $item) {
            $caminho = $item->getRealPath();

            if ($caminho === false) {
                continue;
            }

            $ok = $item->isDir() ? @rmdir($caminho) : @unlink($caminho);

            if (! $ok) {
                return false;
            }
        }

        return @rmdir($dir);
    }

    private function percorrer(string $dir): \RecursiveIteratorIterator
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
    }

    private function formatarBytes(int $bytes): string
    {
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($unidades) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return sprintf('%.1f %s', $bytes, $unidades[$i]);
    }
}
