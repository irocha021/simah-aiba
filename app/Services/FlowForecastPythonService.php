<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class FlowForecastPythonService
{
    protected string $pythonPath;
    protected string $scriptPath;
    protected string $tempDir;

    public function __construct()
    {
        $this->pythonPath = env('PYTHON_PATH', '/usr/bin/python3');
        $this->scriptPath = storage_path('app/scripts/previsao_otimizada.py');
        $this->tempDir = storage_path('app/temp');

        // Criar diretório temp se não existir
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    /**
     * Gera CSV temporário com dados de telemetria
     */
    public function generateCsvFromReadings(array $readings, int $stationCode): string
    {
        $filename = "station_{$stationCode}_" . time() . ".csv";
        $filepath = $this->tempDir . '/' . $filename;

        $fp = fopen($filepath, 'w');

        // Header
        fputcsv($fp, ['Data', 'Hora', 'Chuva (mm)', 'Nivel (cm)', 'Vazao (m3/s)'], ';');

        // Dados
        foreach ($readings as $reading) {
            $date = date('Y-m-d', strtotime($reading->measurement_datetime));
            $time = date('H:i:s', strtotime($reading->measurement_datetime));

            fputcsv($fp, [
                $date,
                $time,
                $reading->adopted_rainfall ?? 0,
                $reading->adopted_quota ?? 0,
                $reading->adopted_flow ?? 0,
            ], ';');
        }

        fclose($fp);

        Log::info("CSV gerado", ['filepath' => $filepath, 'station_code' => $stationCode]);

        return $filepath;
    }

    /**
     * Executa script Python e retorna resultado
     */
    public function executeForecast(
        string $csvPath,
        int $month,
        float $alfaPond,
        float $qNoventa,
        float $vsup
    ): array {
        $command = sprintf(
            '%s %s %s %d %s %s %s 2>&1',
            escapeshellarg($this->pythonPath),
            escapeshellarg($this->scriptPath),
            escapeshellarg($csvPath),
            $month,
            escapeshellarg($alfaPond),
            escapeshellarg($qNoventa),
            escapeshellarg($vsup)
        );

        Log::info('Executando script Python', ['command' => $command]);

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $errorMsg = implode("\n", $output);
            Log::error('Erro ao executar script Python', [
                'return_code' => $returnCode,
                'output' => $errorMsg
            ]);
            throw new Exception("Erro ao executar script Python: {$errorMsg}");
        }

        $jsonOutput = implode("\n", $output);
        $result = json_decode($jsonOutput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Erro ao decodificar JSON: " . json_last_error_msg());
        }

        if (isset($result['error'])) {
            throw new Exception("Erro no script Python: {$result['error']}");
        }

        return $result;
    }

    /**
     * Remove arquivo CSV temporário
     */
    public function cleanupCsv(string $filepath): void
    {
        if (file_exists($filepath)) {
            unlink($filepath);
            Log::info("CSV removido", ['filepath' => $filepath]);
        }
    }
}
