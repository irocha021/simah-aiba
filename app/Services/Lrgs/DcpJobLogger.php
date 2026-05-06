<?php

namespace App\Services\Lrgs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DcpJobLogger
{
    /**
     * Gera um invocation id curto (8 chars) para marcar todas as linhas
     * de uma mesma execução do job. Permite identificar visualmente
     * quando dois jobs rodam em paralelo.
     */
    public function newInvocationId(): string
    {
        return substr(Str::uuid()->toString(), 0, 8);
    }

    public function jobStart(string $invocationId, array $context = []): void
    {
        $this->write($invocationId, 'JOB-START', $context);
    }

    public function jobAlreadyRunning(string $invocationId, array $runningSyncLogIds): void
    {
        $this->write($invocationId, 'JOB-ALREADY-RUNNING', [
            'running_sync_logs' => implode(',', $runningSyncLogIds),
        ]);
    }

    public function stationStart(string $invocationId, int $syncLogId, $station, $start, $end): void
    {
        $this->write($invocationId, 'STATION-START', [
            'sync_log' => $syncLogId,
            'station_id' => $station->id,
            'station_name' => $station->name,
            'address' => $station->dcp_address,
            'period' => sprintf('%s → %s', (string) $start, (string) $end),
        ]);
    }

    public function stationDone(string $invocationId, int $syncLogId, array $stats): void
    {
        $this->write($invocationId, 'STATION-DONE', array_merge(
            ['sync_log' => $syncLogId],
            $stats
        ));
    }

    public function readingInserted(?string $invocationId, ?int $syncLogId, int $stationId, string $readingDatetime): void
    {
        $this->write($invocationId, 'READING-INSERTED', [
            'sync_log' => $syncLogId,
            'station' => $stationId,
            'dt' => $readingDatetime,
        ]);
    }

    public function readingSkippedDuplicate(?string $invocationId, ?int $syncLogId, int $stationId, string $readingDatetime, string $reason): void
    {
        $this->write($invocationId, 'READING-SKIPPED', [
            'sync_log' => $syncLogId,
            'station' => $stationId,
            'dt' => $readingDatetime,
            'reason' => $reason,
        ]);
    }

    public function reprocessStart(string $invocationId, int $count): void
    {
        $this->write($invocationId, 'REPROCESS-START', ['pending_count' => $count]);
    }

    public function reprocessDone(string $invocationId, int $count): void
    {
        $this->write($invocationId, 'REPROCESS-DONE', ['processed_count' => $count]);
    }

    public function jobEnd(string $invocationId, array $totals): void
    {
        $this->write($invocationId, 'JOB-END', $totals);
    }

    public function error(string $invocationId, string $stage, \Throwable $e, array $context = []): void
    {
        $this->write($invocationId, 'ERROR', array_merge($context, [
            'stage' => $stage,
            'message' => $e->getMessage(),
        ]));
    }

    /**
     * Escreve uma linha no canal dcp no formato amigável.
     */
    private function write(?string $invocationId, string $stage, array $fields): void
    {
        $parts = [];
        foreach ($fields as $key => $value) {
            if ($value === null) continue;
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $strValue = (string) $value;
            // Aspas se tiver espaço ou caractere especial
            if (preg_match('/[\s"=]/', $strValue)) {
                $strValue = '"' . str_replace('"', '\\"', $strValue) . '"';
            }
            $parts[] = "{$key}={$strValue}";
        }

        $line = sprintf('[inv=%s] [%s] %s',
            $invocationId ?? '--------',
            $stage,
            implode(' ', $parts)
        );

        Log::channel('dcp')->info($line);
    }
}
