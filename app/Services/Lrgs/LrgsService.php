<?php

namespace App\Services\Lrgs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Exception;

class LrgsService
{
    private string $lrgsPath;
    private string $configFile;
    private string $host;
    private string $username;
    private string $password;
    private string $tempDirectory;

    public function __construct()
    {
        $this->lrgsPath = base_path(config('lrgs.paths.base'));
        $this->configFile = base_path(config('lrgs.paths.config'));
        $this->tempDirectory = storage_path(config('lrgs.paths.temp_directory'));

        $this->host = config('lrgs.credentials.host');
        $this->username = config('lrgs.credentials.username');
        $this->password = config('lrgs.credentials.password');
    }

    /**
     * Executa o comando getDcpMessages e salva saída em arquivo
     *
     * @return string Caminho do arquivo gerado
     * @throws Exception
     */
    public function fetchMessages(): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $outputFile = $this->tempDirectory . "/dcp_messages_{$timestamp}.txt";

        // Garante que o diretório existe
        if (!is_dir($this->tempDirectory)) {
            mkdir($this->tempDirectory, 0755, true);
        }

        // Pega os delimitadores da configuração
        $beforeDelimiter = config('lrgs.delimiters.before');

        // Monta o comando com delimitadores usando aspas duplas para interpretar \n
        $command = sprintf(
            '%s -h %s -u %s -P %s -f %s -b "%s" -a "%s\n" > %s 2>&1',
            escapeshellarg($this->lrgsPath),
            escapeshellarg($this->host),
            escapeshellarg($this->username),
            escapeshellarg($this->password),
            escapeshellarg($this->configFile),
            $beforeDelimiter,
            $beforeDelimiter,
            escapeshellarg($outputFile)
        );

        Log::info('Executando comando LRGS', ['command' => $command]);

        // Executa o comando
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            Log::error('Erro ao executar comando LRGS', [
                'return_code' => $returnCode,
                'output' => $output
            ]);
            throw new Exception("Erro ao executar comando LRGS. Return code: {$returnCode}");
        }

        if (!file_exists($outputFile)) {
            throw new Exception("Arquivo de saída não foi gerado: {$outputFile}");
        }

        Log::info('Mensagens DCP salvas', ['file' => $outputFile]);

        return $outputFile;
    }

    /**
     * Lê o arquivo e retorna as linhas
     *
     * @param string $filePath
     * @return array
     */
    public function readMessagesFromFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("Arquivo não encontrado: {$filePath}");
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);

        // Remove linhas vazias e mensagens de finalização
        $lines = array_filter($lines, function($line) {
            $line = trim($line);
            return !empty($line) &&
                   !str_contains($line, 'Normal termination') &&
                   !str_contains($line, 'Until time reached');
        });

        return array_values($lines);
    }

    /**
     * Executa todo o processo: busca mensagens e retorna parsed
     *
     * @return array
     */
    public function fetchAndParseMessages(): array
    {
        $filePath = $this->fetchMessages();
        $lines = $this->readMessagesFromFile($filePath);

        $parser = new DcpMessageParser();
        $messages = [];

        foreach ($lines as $line) {
            try {
                $parsed = $parser->parse($line);
                if ($parsed) {
                    $messages[] = $parsed;
                }
            } catch (Exception $e) {
                Log::warning('Erro ao parsear linha DCP', [
                    'line' => $line,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Mensagens DCP parseadas', ['total' => count($messages)]);

        return $messages;
    }

    /**
     * Calcula o intervalo da última hora cheia
     * Exemplo: agora 17:40 → retorna ['start' => 16:00, 'end' => 17:00]
     *
     * @return array{start: Carbon, end: Carbon}
     */
    public function getLastFullHourInterval(): array
    {
        $now = Carbon::now('UTC');

        // Hora cheia atual (trunca minutos e segundos)
        $endHour = $now->copy()->startOfHour();

        // Hora cheia anterior (1 hora atrás)
        $startHour = $endHour->copy()->subHour();

        return [
            'start' => $startHour,
            'end' => $endHour,
        ];
    }

    /**
     * Converte data para dia juliano (1-366)
     * Exemplo: 10/Nov/2025 → 314
     *
     * @param \Carbon\Carbon|\Illuminate\Support\Carbon $date
     * @return int
     */
    public function convertToJulianDay($date): int
    {
        return $date->dayOfYear;
    }

    /**
     * Gera arquivo MessageBrowser.sc com os critérios de busca
     *
     * @param string $dcpAddress Endereço DCP (ex: B04041E0)
     * @param \Carbon\Carbon|\Illuminate\Support\Carbon $startTime Data/hora inicial
     * @param \Carbon\Carbon|\Illuminate\Support\Carbon $endTime Data/hora final
     * @return void
     * @throws Exception
     */
    public function generateSearchCriteria(string $dcpAddress, $startTime, $endTime): void
    {
        // Formata as datas no formato LRGS: YYYY/JJJ HH:MM:SS
        $startJulian = $this->convertToJulianDay($startTime);
        $endJulian = $this->convertToJulianDay($endTime);

        $drsSince = sprintf(
            '%d/%03d %s',
            $startTime->year,
            $startJulian,
            $startTime->format('H:i:s')
        );

        $drsUntil = sprintf(
            '%d/%03d %s',
            $endTime->year,
            $endJulian,
            $endTime->format('H:i:s')
        );

        // Endereço em lowercase
        $dcpAddressLower = strtolower($dcpAddress);

        // Conteúdo do arquivo
        $content = "#\n";
        $content .= "# LRGS Search Criteria\n";
        $content .= "#\n";
        $content .= "DRS_SINCE: {$drsSince}\n";
        $content .= "DRS_UNTIL: {$drsUntil}\n";
        $content .= "DCP_ADDRESS: {$dcpAddressLower}\n";

        // Garante que o diretório existe e tem permissão
        $configDir = dirname($this->configFile);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        // Se o arquivo existe mas não tem permissão de escrita, tenta corrigir
        if (file_exists($this->configFile) && !is_writable($this->configFile)) {
            try {
                chmod($this->configFile, 0755);
            } catch (\Exception $e) {
                Log::warning('Não foi possível alterar permissões do arquivo MessageBrowser.sc', [
                    'file' => $this->configFile,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Escreve o arquivo
        $result = file_put_contents($this->configFile, $content);

        if ($result === false) {
            throw new Exception("Erro ao gerar arquivo MessageBrowser.sc. Verifique as permissões do diretório: {$configDir}");
        }

        // Garante permissões corretas para próximas escritas
        try {
            chmod($this->configFile, 0644);
        } catch (\Exception $e) {
            Log::warning('Não foi possível definir permissões do arquivo MessageBrowser.sc', [
                'file' => $this->configFile,
                'error' => $e->getMessage()
            ]);
        }

        Log::info('Arquivo MessageBrowser.sc gerado', [
            'dcp_address' => $dcpAddressLower,
            'start' => $drsSince,
            'end' => $drsUntil,
            'file' => $this->configFile
        ]);
    }
}
