<?php

namespace App\Services;

use App\Enums\StationSourceEnum;
use App\Repositories\Interfaces\HwInventoryStationInterface;
use App\Repositories\Interfaces\DcpStationRepositoryInterface;
use App\Repositories\Interfaces\PocoRimasRepositoryInterface;
use App\Repositories\Interfaces\PocoSiagasRepositoryInterface;
use App\Repositories\Interfaces\CnarhRepositoryInterface;

class StationService
{
    public function __construct(
        protected HwInventoryStationInterface $hwInventoryStationRepository,
        protected DcpStationRepositoryInterface $dcpStationRepository,
        protected PocoRimasRepositoryInterface $pocoRimasRepository,
        protected PocoSiagasRepositoryInterface $pocoSiagasRepository,
        protected CnarhRepositoryInterface $cnarhRepository
    ) {}

    public function getAllForMap(): array
    {
        $stations = [];
        $statistics = [
            'total' => 0,
            'by_source' => []
        ];

        //  HidroWeb Telemetria
        foreach ($this->hwInventoryStationRepository->getTelemetryStationsWithCoordinates() as $station) {
            $source = 'hidroweb_telemetria';
            $stations[] = [
                'code' => (string) $station->station_code,
                'name' => $station->station_name,
                'latitude' => (float) $station->latitude,
                'longitude' => (float) $station->longitude,
                'source' => $source, 
            ];
            $statistics['by_source'][$source] = ($statistics['by_source'][$source] ?? 0) + 1;
        }

        //  HidroWeb Qualidade da Água
        foreach ($this->hwInventoryStationRepository->getQualityStationsWithCoordinates() as $station) {
            $source = 'hidroweb_qualidade_agua';
            $stations[] = [
                'code' => (string) $station->station_code,
                'name' => $station->station_name,
                'latitude' => (float) $station->latitude,
                'longitude' => (float) $station->longitude,
                'source' => $source, 
            ];
            $statistics['by_source'][$source] = ($statistics['by_source'][$source] ?? 0) + 1;
        }

        //  DCP Stations
        foreach ($this->dcpStationRepository->getAllWithCoordinates() as $station) {
            $source = StationSourceEnum::DCP->value;
            $stations[] = [
                'code' => $station->dcp_address,
                'name' => $station->station_label ?? $station->station_name,
                'latitude' => (float) $station->latitude,
                'longitude' => (float) $station->longitude,
                'source' => $source,
            ];
            $statistics['by_source'][$source] = ($statistics['by_source'][$source] ?? 0) + 1;
        }

        //  HidroWeb Telemetria com Previsão
        foreach ($this->hwInventoryStationRepository->getTelemetryStationsWithForecastCoordinates() as $station) {
            $source = StationSourceEnum::HIDROWEB_TELEMETRIA_COM_PREVISAO->value ;
            $stations[] = [
                'code' => (string) $station->station_code,
                'name' => $station->station_name,
                'latitude' => (float) $station->latitude,
                'longitude' => (float) $station->longitude,
                'source' => $source, 
            ];
            $statistics['by_source'][$source] = ($statistics['by_source'][$source] ?? 0) + 1;
        }

        //  RIMAS
          foreach ($this->pocoRimasRepository->getAllWithCoordinates() as $station) {
              $source = StationSourceEnum::RIMAS->value;
              $stations[] = [
                  'code' => (string) $station->id_ponto,
                  'name' => $station->id_ponto ? 'Poço RIMAS #' . $station->id_ponto : 'Poço RIMAS',
                  'latitude' => (float) $station->latitude_d,
                  'longitude' => (float) $station->longitude,
                  'source' => $source,
              ];
              $statistics['by_source'][$source] = ($statistics['by_source'][$source] ?? 0) + 1;
          }

        //  SIAGAS
         foreach ($this->pocoSiagasRepository->getAllWithCoordinates() as $station) {
              $source = StationSourceEnum::SIAGAS->value;
              $stations[] = [
                  'code' => (string) $station->ponto,
                  'name' => $station->localizaca ?? ($station->ponto ? 'Poço SIAGAS #' . $station->ponto : 'Poço SIAGAS'),
                  'latitude' => (float) $station->latitude_d,
                  'longitude' => (float) $station->longitude_,
                  'source' => $source,
              ];
              $statistics['by_source'][$source] = ($statistics['by_source'][$source] ?? 0) + 1;
         }

        //  CNARH
        foreach ($this->cnarhRepository->getAllWithCoordinates() as $station) {
            $source = StationSourceEnum::CNARH->value;
            $stations[] = [
                'code' => (string) $station->int_cd_cnarh40,
                'name' => $station->emp_nm_empreendimento ?? 'CNARH #' . $station->int_nu_cnarh,
                'latitude' => (float) $station->int_nu_latitude,
                'longitude' => (float) $station->int_nu_longitude,
                'source' => $source,
            ];
            $statistics['by_source'][$source] = ($statistics['by_source'][$source] ?? 0) + 1;
        }

        $statistics['total'] = count($stations);

        return [
            'stations' => $stations,
            'statistics' => $statistics
        ];
    }

    public function getCnarhStations(): array
    {
        $stations = [];
        foreach ($this->cnarhRepository->getAllWithCoordinates() as $station) {
            $stations[] = [
                'code' => (string) $station->int_cd_cnarh40,
                'name' => $station->emp_nm_empreendimento ?? 'CNARH #' . $station->int_nu_cnarh,
                'latitude' => (float) $station->int_nu_latitude,
                'longitude' => (float) $station->int_nu_longitude,
                'source' => 'cnarh',
            ];
        }
        return $stations;
    }

    public function getPocosRimasStations(): array
    {
        $stations = [];
        foreach ($this->pocoRimasRepository->getAllWithCoordinates() as $station) {
            $stations[] = [
                'code' => (string) $station->id_ponto,
                'name' => $station->id_ponto ? 'Poço RIMAS #' . $station->id_ponto : 'Poço RIMAS',
                'latitude' => (float) $station->latitude_d,
                'longitude' => (float) $station->longitude,
                'source' => 'pocos_rimas',
            ];
        }
        return $stations;
    }

    public function getPocosSiagasStations(): array
    {
        $stations = [];
        foreach ($this->pocoSiagasRepository->getAllWithCoordinates() as $station) {
            $stations[] = [
                'code' => (string) $station->ponto,
                'name' => $station->localizaca ?? ($station->ponto ? 'Poço SIAGAS #' . $station->ponto : 'Poço SIAGAS'),
                'latitude' => (float) $station->latitude_d,
                'longitude' => (float) $station->longitude_,
                'source' => 'pocos_siagas',
            ];
        }
        return $stations;
    }

    public function getHidrowebTelemetriaStations(): array
    {
        $stations = [];
        foreach ($this->hwInventoryStationRepository->getTelemetryStationsWithCoordinates() as $station) {
            $stations[] = [
                'code' => (string) $station->station_code,
                'name' => $station->station_name,
                'latitude' => (float) $station->latitude,
                'longitude' => (float) $station->longitude,
                'source' => 'hidroweb_telemetria',
            ];
        }
        return $stations;
    }

    public function getHidrowebQualidadeAguaStations(): array
    {
        $stations = [];
        foreach ($this->hwInventoryStationRepository->getQualityStationsWithCoordinates() as $station) {
            $stations[] = [
                'code' => (string) $station->station_code,
                'name' => $station->station_name,
                'latitude' => (float) $station->latitude,
                'longitude' => (float) $station->longitude,
                'source' => 'hidroweb_qualidade_agua',
            ];
        }
        return $stations;
    }

    public function getHidrowebTelemetriaPrevisaoStations(): array
    {
        $stations = [];
        foreach ($this->hwInventoryStationRepository->getTelemetryStationsWithForecastCoordinates() as $station) {
            $stations[] = [
                'code' => (string) $station->station_code,
                'name' => $station->station_name,
                'latitude' => (float) $station->latitude,
                'longitude' => (float) $station->longitude,
                'source' => 'hidroweb_telemetria_com_previsao',
            ];
        }
        return $stations;
    }

    public function getLrgsClientStations(): array
    {
        $stations = [];
        foreach ($this->dcpStationRepository->getAllWithCoordinates() as $station) {
            $stations[] = [
                'code' => $station->dcp_address,
                'name' => $station->station_label ?? $station->station_name,
                'latitude' => (float) $station->latitude,
                'longitude' => (float) $station->longitude,
                'source' => 'lrgs_client',
            ];
        }
        return $stations;
    }

}
