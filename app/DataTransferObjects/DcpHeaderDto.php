<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;

/**
 * Armazena os dados extraídos de uma string de mensagem GOES DCS
 * (Formato DOMSAT/LRIT/Network).
 *
 * Baseado no Apêndice F.2 (páginas 69-71) do documento PDF.
 */
class DcpHeaderDto
{
    public function __construct(
        // Campos brutos extraídos 
        public readonly string $address,
        public readonly int $year,
        public readonly int $julianDay,
        public readonly int $hour,
        public readonly int $minute,
        public readonly int $second,
        public readonly string $failureCode,
        public readonly string $signalStrength,
        public readonly string $frequencyOffset,
        public readonly string $modulationIndex,
        public readonly string $dataQuality,
        public readonly string $channel,
        public readonly string $spacecraft,
        public readonly string $receptionSource,
        public readonly int $dataLength,

        // Campos traduzidos/processados
        // public readonly Carbon $timestamp,
        // public readonly string $failureCodeDescription,
        // public readonly string $modulationIndexDescription,
        // public readonly string $dataQualityDescription,
        // public readonly string $spacecraftDescription,
        // public readonly string $receptionSourceDescription
    ) {
    }
}