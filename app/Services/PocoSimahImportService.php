<?php

namespace App\Services;

use App\Repositories\Interfaces\PocoSimahReadingRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class PocoSimahImportService
{
    protected $readingRepository;

    public function __construct(PocoSimahReadingRepositoryInterface $readingRepository)
    {
        $this->readingRepository = $readingRepository;
    }

    public function import(int $stationId, UploadedFile $file): array
    {
        $lines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($lines)) {
            return ['imported' => 0, 'errors' => ['Arquivo vazio.']];
        }

        // Remove cabeçalho
        array_shift($lines);

        $records = [];
        $errors  = [];

        foreach ($lines as $lineNumber => $line) {
            try {
                $cols = explode(';', $line);

                if (count($cols) < 8) {
                    $errors[] = "Linha " . ($lineNumber + 2) . ": colunas insuficientes.";
                    continue;
                }

                $datetimeLocal = $this->parseDateTime(trim($cols[1]));
                $datetimeUtc   = $this->parseDateTimeUtc(trim($cols[2]));

                if (!$datetimeUtc) {
                    $errors[] = "Linha " . ($lineNumber + 2) . ": data/hora UTC inválida.";
                    continue;
                }

                $records[] = [
                    'number'         => isset($cols[0]) ? (int) trim($cols[0]) : null,
                    'datetime_local' => $datetimeLocal,
                    'datetime_utc'   => $datetimeUtc,
                    'pd_bar'         => $this->parseDecimal($cols[3]),
                    'p1_bar'         => $this->parseDecimal($cols[4]),
                    'p2_bar'         => $this->parseDecimal($cols[5]),
                    'tob1_celsius'   => $this->parseDecimal($cols[6]),
                    'tob2_celsius'   => $this->parseDecimal($cols[7]),
                ];

            } catch (\Exception $e) {
                $errors[] = "Linha " . ($lineNumber + 2) . ": " . $e->getMessage();
            }
        }

        $imported = 0;

        if (!empty($records)) {
            $imported = $this->readingRepository->upsertBatch($stationId, $records);
        }

        Log::info('Importação SIMAH concluída', [
            'station_id' => $stationId,
            'imported'   => $imported,
            'errors'     => count($errors),
        ]);

        return ['imported' => $imported, 'errors' => $errors];
    }

    private function parseDateTime(string $value): ?string
    {
        // Formato: "2026-04-07 14:25:29,0" → remove a parte decimal
        $value = preg_replace('/,\d+$/', '', $value);
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        return $dt ? $dt->format('Y-m-d H:i:s') : null;
    }

    private function parseDateTimeUtc(string $value): ?string
    {
        // Formato ISO: "2026-04-07T17:25:29,000Z"
        $value = preg_replace('/,\d+Z$/', 'Z', $value);
        $dt = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $value);
        return $dt ? $dt->format('Y-m-d H:i:s') : null;
    }

    private function parseDecimal(string $value): ?float
    {
        $value = trim($value);
        if ($value === '' || $value === '-') {
            return null;
        }
        // Troca vírgula decimal por ponto
        return (float) str_replace(',', '.', $value);
    }
}
