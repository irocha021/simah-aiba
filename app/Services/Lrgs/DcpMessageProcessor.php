<?php

namespace App\Services\Lrgs;

use App\DataTransferObjects\DcpHeaderDto;
use App\Repositories\Interfaces\DcpStationRepositoryInterface;
use Illuminate\Support\Facades\Log;

class DcpMessageProcessor
{
    private DcpHeaderParser $headerParser;
    private DcpStationRepositoryInterface $dcpStationRepository;
    public array $pendingFlowRecord = [];


    public function __construct(
        DcpHeaderParser $headerParser,
        DcpStationRepositoryInterface $dcpStationRepository
    ) {
        $this->headerParser = $headerParser;
        $this->dcpStationRepository = $dcpStationRepository;
    }

    /**
     * Processa uma mensagem DCP e retorna array para inserção no banco
     *
     * @param string $message
     * @return array|null Array com dados para inserção ou null se mensagem for corrompida
     */
    public function processMessage(string $message): ?array
    {
        try {
            // Divide a mensagem por ; e aplica trim em cada parte
            $parts = array_map('trim', explode(";", $message));

            // Valida se mensagem está corrompida
            if ($this->isCorrupted($message, $parts)) {
                // Log::warning('Mensagem DCP corrompida ignorada', [
                //     'message_preview' => substr($message, 0, 200)
                // ]);
                return null;
            }

            // Parse do header
            $header = $this->headerParser->parse($parts[0]);

            // Busca a estação pelo endereço
            $station = $this->dcpStationRepository->findByDcpAddress($header->address);

            if (!$station) {
                Log::warning('Estação DCP não encontrada', [
                    'address' => $header->address
                ]);
                return null;
            }

            // Atualiza lat/long da estação se ainda não preenchidos
            if (is_null($station->latitude) || is_null($station->longitude)) {
                $lat = $this->parseNumericValue($parts[23] ?? null);
                $lng = $this->parseNumericValue($parts[24] ?? null);

             
                if ($lat !== null && $lng !== null) {
                    $station->update(['latitude' => $lat, 'longitude' => $lng]);
                    $station->latitude = $lat;
                    $station->longitude = $lng;
                }
            }

            // Monta o array de dados para inserção
            return $this->mapToDatabase($parts, $header, $station);

        } catch (\Exception $e) {
            Log::error('Erro ao processar mensagem DCP', [
                'error' => $e->getMessage(),
                'message_preview' => substr($message, 0, 200)
            ]);
            return null;
        }
    }

    
    /**
     * Verifica se a mensagem está corrompida
     *
     * @param array $parts
     * @return bool
     */
    private function isCorrupted(string $message, array $parts): bool
    {
        if (substr_count($message, '$') > 4) {
            return true;
        }

        if (empty($parts) || empty($parts[0])) {
            return true;
        }

        $header = $parts[0];

        // Verifica se tem o padrão mínimo esperado no header
        // Header deve começar com endereço DCP (8 caracteres hexadecimais)
        if (!preg_match('/^[A-F0-9]{8}/', $header)) {
            Log::warning('Header DCP corrompido', ['header' => $header]);
            return true;
        }

        // Verifica os campos de dados numéricos (índices 1-22)
        // Estes campos devem conter apenas números, pontos, espaços, ou "NAN"
        for ($i = 1; $i <= 22 && $i < count($parts); $i++) {
            $field = trim($parts[$i]);
            
            // Pula campos vazios (permitido)
            if ($field === '') {
                continue;
            }

            // Permite "NAN" (case insensitive)
            if (strtoupper($field) === 'NAN') {
                continue;
            }

            // Verifica se contém caracteres de controle ou especiais suspeitos
            // Inclui: $, ~, _, [, ], {, }, |, \, e caracteres de controle (0x00-0x1F)
            $suspiciousChars = preg_match_all('/[\$~_\[\]\{\}\|\\\\' . "\x00-\x1F" . ']/', $field);
            
            // Se tiver mais de 2 caracteres especiais suspeitos, considera corrompido
            if ($suspiciousChars > 2) {
                Log::warning('Header DCP corrompido', ['header' => $header]);
                return true;
            }

            // Para campos que deveriam ser numéricos (1-22), verifica se tem padrão válido
            // Deve ser: número inteiro, decimal, negativo, ou vazio
            if ($i >= 1 && $i <= 22) {
                // Remove espaços para validação
                $cleanField = str_replace(' ', '', $field);
                
                // Verifica se é um número válido (aceita negativos e decimais)
                if (!preg_match('/^-?\d+\.?\d*$/', $cleanField) && strtoupper($cleanField) !== 'NAN') {
                    // Se não é número válido, verifica se tem letras ou caracteres estranhos
                    // Campos numéricos não devem ter letras (exceto NAN já tratado)
                    if (preg_match('/[a-zA-Z]/', $cleanField) || strlen($cleanField) > 0) {
                        Log::warning('Header DCP corrompido', ['header' => $header]);
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Mapeia os dados do array para estrutura do banco de dados
     *
     * @param array $parts
     * @param DcpHeaderDto $header
     * @param int $stationId
     * @return array
     */
    private function mapToDatabase(array $parts, DcpHeaderDto $header, $station): array
    {
        $now = now();

        $fullYear = $header->year < 100 ? 2000 + $header->year : $header->year;
        $readingDatetime = \Carbon\Carbon::create($fullYear, 1, 1, $header->hour, $header->minute, $header->second, 'UTC')
            ->addDays($header->julianDay - 1)
            ->subHours(3);

        return [
            // Foreign key
            'dcp_station_id' => $station->id,


            // Raw header
            'raw_header' => $parts[0] ?? null,

            // Header data
            'address' => $header->address,
            'reading_datetime' => $readingDatetime,
            'year' => $header->year,
            'julian_day' => $header->julianDay,
            'hour' => $header->hour,
            'minute' => $header->minute,
            'second' => $header->second,
            'failure_code' => $header->failureCode,
            'signal_strength' => $header->signalStrength,
            'frequency_offset' => $header->frequencyOffset,
            'modulation_index' => $header->modulationIndex,
            'data_quality' => $header->dataQuality,
            'channel' => $header->channel,
            'spacecraft' => $header->spacecraft,
            'reception_source' => $header->receptionSource,
            'data_length' => $header->dataLength,

            // Water level readings (indices 1-8)
            'water_level_120min' => $this->parseNumericValue($parts[1] ?? null),
            'water_level_105min' => $this->parseNumericValue($parts[2] ?? null),
            'water_level_90min' => $this->parseNumericValue($parts[3] ?? null),
            'water_level_75min' => $this->parseNumericValue($parts[4] ?? null),
            'water_level_60min' => $waterLevel60 = $this->parseNumericValue($parts[5] ?? null),
            'water_level_45min' => $waterLevel45 = $this->parseNumericValue($parts[6] ?? null),
            'water_level_30min' => $waterLevel30 = $this->parseNumericValue($parts[7] ?? null),
            'water_level_15min' => $waterLevel15 = $this->parseNumericValue($parts[8] ?? null),
            'flow' => $this->calculateFlow(
                $station, 
                $readingDatetime, 
                $waterLevel15, 
                $waterLevel30, 
                $waterLevel45, 
                $waterLevel60
            ),



            // Rain readings (indices 9-16)
            'rain_120min' => $this->parseNumericValue($parts[9] ?? null),
            'rain_105min' => $this->parseNumericValue($parts[10] ?? null),
            'rain_90min' => $this->parseNumericValue($parts[11] ?? null),
            'rain_75min' => $this->parseNumericValue($parts[12] ?? null),
            'rain_60min' => $this->parseNumericValue($parts[13] ?? null),
            'rain_45min' => $this->parseNumericValue($parts[14] ?? null),
            'rain_30min' => $this->parseNumericValue($parts[15] ?? null),
            'rain_15min' => $this->parseNumericValue($parts[16] ?? null),

            // Temperature and sensors (indices 17-19)
            'water_temperature' => $this->parseNumericValue($parts[17] ?? null),
            'internal_temperature' => $this->parseNumericValue($parts[18] ?? null),
            'battery_voltage' => $this->parseNumericValue($parts[19] ?? null),

            // Station measurements (indices 20-22)
            'level_adjustment' => $this->parseNumericValue($parts[20] ?? null),
            'display_value' => $this->parseStringValue($parts[21] ?? null),
            'atmospheric_pressure' => $this->parseNumericValue($parts[22] ?? null),

            // Location (indices 23-24)
            'latitude' => $this->parseNumericValue($parts[23] ?? null),
            'longitude' => $this->parseNumericValue($parts[24] ?? null),

            // Device information (indices 25-36)
            'door_sensor_open' => $this->parseBooleanValue($parts[25] ?? null),
            'serial_number' => $this->parseStringValue($parts[26] ?? null),
            'program_signature' => $this->parseStringValue($parts[27] ?? null),
            'skipped_scan' => $this->parseIntegerValue($parts[28] ?? null),
            'operating_system_version' => $this->parseStringValue($parts[29] ?? null),
            'transmitter_serial_number' => $this->parseStringValue($parts[30] ?? null),
            'firmware_version' => $this->parseStringValue($parts[31] ?? null),
            'goes_antenna_signal' => $this->parseStringValue($parts[32] ?? null),
            'program_version' => $this->parseStringValue($parts[33] ?? null),
            'restart_time' => $this->parseStringValue($parts[34] ?? null),
            'sensor_type' => $this->parseStringValue($parts[35] ?? null),
            'extra' => $this->parseStringValue($parts[36] ?? null),

            // Timestamps (necessário para bulk insert)
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function calculateFlow(
        $station, 
        \Carbon\Carbon $readingDatetime, 
        ?float $waterLevel15, 
        ?float $waterLevel30, 
        ?float $waterLevel45, 
        ?float $waterLevel60
    ): ?float {
        // Busca curva vigente na data da leitura
        $curve = $station->ratingCurves()
            ->where('starts_at', '<=', $readingDatetime->toDateString())
            ->where(function ($q) use ($readingDatetime) {
                $q->whereNull('ends_at')
                ->orWhere('ends_at', '>=', $readingDatetime->toDateString());
            })
            ->first();

        if (!$curve) {
            return null;
        }

        // Fallback: usa o primeiro water level disponível na sequência
        $waterLevel = null;
        $interval   = null;

        if ($waterLevel15 !== null) {
            $waterLevel = $waterLevel15;
            $interval   = 15;
        } elseif ($waterLevel30 !== null) {
            $waterLevel = $waterLevel30;
            $interval   = 30;
        } elseif ($waterLevel45 !== null) {
            $waterLevel = $waterLevel45;
            $interval   = 45;
        } elseif ($waterLevel60 !== null) {
            $waterLevel = $waterLevel60;
            $interval   = 60;
        }

        if ($waterLevel === null) {
            return null;
        }

        // Calcula a vazão
        if ($curve->curva_chave == 1) {
            $flow = \App\Helpers\Equations::calcularConversaoDaCargaHidraulicaEmVazaoDeRioPrimeira(
                (float) $curve->a,
                (float) $curve->b,
                $waterLevel,
                (float) $curve->h0
            );
        } elseif ($curve->curva_chave == 2) {
            $flow = \App\Helpers\Equations::calcularConversaoDaCargaHidraulicaEmVazaoDeRioSegunda(
                (float) $curve->a,
                (float) $curve->b,
                (float) $curve->c,
                $waterLevel
            );
        } else {
            return null;
        }

        // Grava rastreamento (após o reading ser inserido, via observer ou após insert)
        $this->pendingFlowRecord = [
            'dcp_station_rating_curve_id' => $curve->id,
            'water_level_used'            => $waterLevel,
            'water_level_interval'        => $interval,
            'flow'                        => $flow,
        ];

        return $flow;
    }

    /**
     * Parse de valor numérico (converte NAN e vazios para null)
     */
    private function parseNumericValue(?string $value): ?float
    {
        // Trim e verifica se é null ou vazio
        if ($value === null || trim($value) === '' || strtoupper(trim($value)) === 'NAN') {
            return null;
        }

        $numeric = filter_var($value, FILTER_VALIDATE_FLOAT);
        return $numeric !== false ? $numeric : null;
    }

    /**
     * Parse de valor inteiro
     */
    private function parseIntegerValue(?string $value): ?int
    {
        // Trim e verifica se é null ou vazio
        if ($value === null || trim($value) === '' || strtoupper(trim($value)) === 'NAN') {
            return null;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);
        return $integer !== false ? $integer : null;
    }

    /**
     * Parse de valor booleano (converte 0/1 para false/true)
     */
    private function parseBooleanValue(?string $value): ?bool
    {
        // Trim e verifica se é null ou vazio
        if ($value === null || trim($value) === '') {
            return null;
        }

        $trimmed = trim($value);

        // Converte string numérica para boolean
        if ($trimmed === '0') {
            return false;
        }
        if ($trimmed === '1') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * Parse de valor string (retorna null se vazio)
     */
    private function parseStringValue(?string $value): ?string
    {
        return !empty($value) ? $value : null;
    }
}
