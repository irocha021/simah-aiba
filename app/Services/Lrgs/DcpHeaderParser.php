<?php

namespace App\Services\Lrgs;

use App\DataTransferObjects\DcpHeaderDto;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Faz o parsing de strings de cabeçalho GOES DCS (Formato DOMSAT/LRIT/Network).
 *
 * A lógica é baseada no Apêndice F.2, "DCS Distribution Format for DOMSAT/LRIT/Network",
 * encontrado nas páginas 69-71 do documento de descrição do sistema GOES DCS.
 */
class DcpHeaderParser
{
    // Define os comprimentos de cada campo no cabeçalho
    private const HEADER_LENGTHS = [
        'address' => 8,
        'year' => 2,
        'julianDay' => 3,
        'hour' => 2,
        'minute' => 2,
        'second' => 2,
        'failureCode' => 1,
        'signalStrength' => 2,
        'frequencyOffset' => 2,
        'modulationIndex' => 1,
        'dataQuality' => 1,
        'channel' => 3,
        'spacecraft' => 1,
        'receptionSource' => 2,
        'dataLength' => 5,
    ];

    /**
     * Faz o parsing da string do cabeçalho da mensagem GOES DCS.
     *
     * @param string $message O cabeçalho da mensagem (37 bytes).
     * @return GoesDcsMessageDto
     * @throws InvalidArgumentException Se a string da mensagem for inválida.
     */
    public function parse(string $message): DcpHeaderDto
    {
        $expectedLength = array_sum(self::HEADER_LENGTHS);
        if (strlen($message) < $expectedLength) {
            throw new InvalidArgumentException("A string da mensagem é muito curta. Esperado: {$expectedLength} bytes.");
        }

        // Extrai os campos brutos usando substr
        $raw = [];
        $offset = 0;
        foreach (self::HEADER_LENGTHS as $field => $length) {
            $raw[$field] = substr($message, $offset, $length);
            $offset += $length;
        }

        // Processa e traduz os campos
        $year = (int) $raw['year'] + 2000; // Assume anos 20xx
        $timestamp = Carbon::createFromFormat('Y-z H:i:s', sprintf(
            '%d-%d %02d:%02d:%02d',
            $year,
            (int) $raw['julianDay'] - 1, // Carbon trata dia juliano como 0-indexado
            (int) $raw['hour'],
            (int) $raw['minute'],
            (int) $raw['second']
        ), 'UTC');

        // Cria e retorna o DTO
        return new DcpHeaderDto(
            address: $raw['address'],
            year: (int) $raw['year'],
            julianDay: (int) $raw['julianDay'],
            hour: (int) $raw['hour'],
            minute: (int) $raw['minute'],
            second: (int) $raw['second'],
            failureCode: $raw['failureCode'],
            signalStrength: $raw['signalStrength'],
            frequencyOffset: $raw['frequencyOffset'],
            modulationIndex: $raw['modulationIndex'],
            dataQuality: $raw['dataQuality'],
            channel: $raw['channel'],
            spacecraft: $raw['spacecraft'],
            receptionSource: $raw['receptionSource'],
            dataLength: (int) $raw['dataLength'],

            // Campos traduzidos
            //timestamp: $timestamp,
            //failureCodeDescription: $this->translateFailureCode($raw['failureCode']),
            //modulationIndexDescription: $this->translateModulationIndex($raw['modulationIndex']),
            //dataQualityDescription: $this->translateDataQuality($raw['dataQuality']),
            //spacecraftDescription: $this->translateSpacecraft($raw['spacecraft']),
            //receptionSourceDescription: $this->translateReceptionSource($raw['receptionSource'])
        );
    }

    /**
     * [cite_start]Traduz o código de falha[cite: 1356].
     */
    private function translateFailureCode(string $code): string
    {
        return match ($code) {
            'G' => 'Good message (Mensagem boa)',
            '?' => 'Message received with parity errors (Mensagem com erros de paridade)',
            'W' => 'Message received on wrong channel (Mensagem no canal errado)',
            'D' => 'Message received on multiple channels (duplicada)',
            'A' => 'Message received with address error(s) (corrigível)',
            'T' => 'Message received late/early (time error)',
            'U' => 'Unexpected message received (inesperada)',
            'N' => 'PDT incomplete (user required data is missing)',
            'M' => 'Scheduled message is missing (Mensagem agendada ausente)',
            default => 'Unknown code',
        };
    }

    /**
     * [cite_start]Traduz o índice de modulação[cite: 1358].
     */
    private function translateModulationIndex(string $code): string
    {
        return match ($code) {
            'N' => 'Normal (60° 9°)',
            'L' => 'Low (<50°)',
            'H' => 'High (>70°)',
            default => 'Unknown code',
        };
    }

    /**
     * [cite_start]Traduz a qualidade dos dados[cite: 1358].
     */
    private function translateDataQuality(string $code): string
    {
        return match ($code) {
            'N' => 'Normal (error rate better than 1 X 10-6)',
            'F' => 'Fair (error rate between 1 X 10-4 and 1 X 10-6)',
            'P' => 'Poor (error rate worse than 1 X 10-4)',
            default => 'Unknown code',
        };
    }

    /**
     * [cite_start]Traduz o código da espaçonave[cite: 1358].
     */
    private function translateSpacecraft(string $code): string
    {
        return match ($code) {
            'E' => 'GOES East',
            'W' => 'Goes West',
            default => 'Unknown code',
        };
    }

    /**
     * [cite_start]Traduz a fonte de recepção[cite: 1360].
     */
    private function translateReceptionSource(string $code): string
    {
        return match ($code) {
            'UB' => 'GOES East & West-WCDAS Backup, Wallops Island',
            'UP' => 'GOES East & West-WCDAS Primary, Wallops Island',
            'XE' => 'GOES East, EDDN, Sioux Falls',
            'XW' => 'GOES West, EDDN, Sioux Falls',
            // Adicione outros códigos da tabela (página 71) se necessário
            default => 'Unknown source',
        };
    }
}