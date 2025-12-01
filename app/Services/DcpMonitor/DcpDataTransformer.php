<?php

namespace App\Services\DcpMonitor;

use Carbon\Carbon;

class DcpDataTransformer
{
    /**
     * Transforma dados completos para output
     */
    public function transformCompleteData(array $data, string $dcpAddress): array
    {
        $transformedData = [
            'dcp_address_searched' => $dcpAddress,
            'fetched_at' => Carbon::now()->toIso8601String(),
            'metadata' => $data['metadata'],
            'transmissions' => $data['transmissions'],
            'utc_time' => $data['utc_time'],
            //'statistics' => $this->calculateStatistics($data['transmissions']),
            //'summary' => $this->generateSummary($data)
        ];

        return $transformedData;
    }

    /**
     * Calcula estatísticas das transmissões
     */
    public function calculateStatistics(array $transmissions): array
    {
        $total = count($transmissions);
        $successful = 0;
        $missing = 0;
        $withMessages = 0;
        $withRawData = 0;
        $signalStrengths = [];
        
        foreach ($transmissions as $trans) {
            if ($trans['is_successful']) {
                $successful++;
            }
            if ($trans['is_missing']) {
                $missing++;
            }
            if ($trans['has_message']) {
                $withMessages++;
            }
            if (isset($trans['raw_data']) && $trans['raw_data']) {
                $withRawData++;
            }
            if ($trans['signal_strength'] !== null) {
                $signalStrengths[] = $trans['signal_strength'];
            }
        }
        
        return [
            'total_transmissions' => $total,
            'successful_transmissions' => $successful,
            'missing_transmissions' => $missing,
            'transmissions_with_messages' => $withMessages,
            'transmissions_with_raw_data' => $withRawData,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            'message_rate' => $total > 0 ? round(($withMessages / $total) * 100, 2) : 0,
            'average_signal_strength' => !empty($signalStrengths) 
                ? round(array_sum($signalStrengths) / count($signalStrengths), 2) 
                : null,
            'min_signal_strength' => !empty($signalStrengths) ? min($signalStrengths) : null,
            'max_signal_strength' => !empty($signalStrengths) ? max($signalStrengths) : null,
        ];
    }

    /**
     * Gera resumo dos dados
     */
    public function generateSummary(array $data): array
    {
        $lastSuccessful = null;
        $lastMissing = null;
        $consecutiveMissing = 0;
        $currentStreak = 0;
        
        // Analisa transmissões de forma reversa (mais recentes primeiro)
        $reversedTransmissions = array_reverse($data['transmissions']);
        
        foreach ($reversedTransmissions as $trans) {
            if ($trans['is_successful'] && !$lastSuccessful) {
                $lastSuccessful = [
                    'date' => $trans['date'],
                    'time' => $trans['transmit_start']['time'],
                ];
            }
            
            if ($trans['is_missing']) {
                if (!$lastMissing) {
                    $lastMissing = [
                        'date' => $trans['date'],
                        'window_start' => $trans['window_start'],
                    ];
                }
                $currentStreak++;
            } else {
                if ($currentStreak > $consecutiveMissing) {
                    $consecutiveMissing = $currentStreak;
                }
                $currentStreak = 0;
            }
        }
        
        // Verifica se ainda está em streak de falhas
        if ($currentStreak > $consecutiveMissing) {
            $consecutiveMissing = $currentStreak;
        }
        
        return [
            'station_name' => $data['metadata']['dcp_address'] ?? null,
            'transmission_interval' => $data['metadata']['transmission_interval'] ?? null,
            'last_successful_transmission' => $lastSuccessful,
            'last_missing_transmission' => $lastMissing,
            'consecutive_missing_count' => $consecutiveMissing,
            'current_status' => $this->determineStatus($reversedTransmissions[0] ?? null),
            'alert_level' => $this->determineAlertLevel($consecutiveMissing),
        ];
    }

    /**
     * Determina status atual da estação
     */
    private function determineStatus(?array $lastTransmission): string
    {
        if (!$lastTransmission) {
            return 'NO_DATA';
        }
        
        if ($lastTransmission['is_successful']) {
            return 'ONLINE';
        }
        
        if ($lastTransmission['is_missing']) {
            return 'OFFLINE';
        }
        
        return 'ERROR';
    }

    /**
     * Determina nível de alerta baseado em transmissões perdidas
     */
    private function determineAlertLevel(int $consecutiveMissing): string
    {
        if ($consecutiveMissing === 0) {
            return 'NORMAL';
        } elseif ($consecutiveMissing <= 2) {
            return 'WARNING';
        } elseif ($consecutiveMissing <= 5) {
            return 'ALERT';
        } else {
            return 'CRITICAL';
        }
    }

    /**
     * Formata dados de uma única transmissão para exibição
     */
    public function formatTransmission(array $transmission): array
    {
        return [
            'datetime' => $this->formatDateTime($transmission['date'], $transmission['transmit_start']['time']),
            'status' => $this->getTransmissionStatus($transmission),
            'signal_quality' => $this->evaluateSignalQuality($transmission['signal_strength']),
            'has_data' => $transmission['has_message'],
            'raw_data_available' => isset($transmission['raw_data']) && $transmission['raw_data'] !== null,
        ];
    }

    /**
     * Formata data e hora
     */
    private function formatDateTime(string $date, string $time): ?string
    {
        try {
            if ($time === '--:--:--' || empty($date)) {
                return null;
            }
            
            $dateTime = Carbon::createFromFormat('m/d/Y H:i:s.u', $date . ' ' . $time);
            return $dateTime->toIso8601String();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtém status da transmissão
     */
    private function getTransmissionStatus(array $transmission): string
    {
        $codeMap = [
            'G' => 'GOOD',
            'M' => 'MISSING',
            '?' => 'PARITY_ERROR',
            'A' => 'ADDRESS_ERROR',
            'B' => 'BAD_ADDRESS',
            'D' => 'DUPLICATED',
            'T' => 'TIME_ERROR',
            'W' => 'WRONG_CHANNEL',
            'S' => 'LOW_SIGNAL',
            'V' => 'LOW_BATTERY',
        ];
        
        return $codeMap[$transmission['failure_code']] ?? 'UNKNOWN';
    }

    /**
     * Avalia qualidade do sinal
     */
    private function evaluateSignalQuality(?int $signalStrength): string
    {
        if ($signalStrength === null) {
            return 'NO_SIGNAL';
        }
        
        if ($signalStrength >= 40) {
            return 'EXCELLENT';
        } elseif ($signalStrength >= 38) {
            return 'GOOD';
        } elseif ($signalStrength >= 35) {
            return 'FAIR';
        } else {
            return 'POOR';
        }
    }
}