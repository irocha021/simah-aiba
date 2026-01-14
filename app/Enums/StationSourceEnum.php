<?php

namespace App\Enums;

enum StationSourceEnum: string
{
    case HIDROWEB_AGUA = 'hidroweb_qualidade_agua';
    case HIDROWEB_TELEMETRIA = 'hidroweb_telemetria';
    case HIDROWEB_TELEMETRIA_COM_PREVISAO = 'hidroweb_telemetria_com_previsao';
    case DCP = 'lrgs_client';
    case RIMAS = 'pocos_rimas'; 
    case SIAGAS = 'pocos_siagas';
    case CNARH = 'cnarh';

    public function getLabel(): string
    {
        return match($this) {
            self::HIDROWEB_AGUA => 'HidroWeb - Qualidade da Água',
            self::HIDROWEB_TELEMETRIA => 'HidroWeb - Telemetria',
            self::HIDROWEB_TELEMETRIA_COM_PREVISAO => 'HidroWeb - Telemetria com Previsão',
            self::DCP => 'LRGS Client',
            self::RIMAS => 'Poços RIMAS',
            self::SIAGAS => 'Poços SIAGAS',
            self::CNARH => 'CNARH',
        };
    }
}
