<?php

namespace App\Services;

use App\Repositories\Interfaces\HwInventoryStationInterface;

class HidroInventoryStationService
{
    protected HwInventoryStationInterface $hwInventoryStationRepository;

    public function __construct(
        HwInventoryStationInterface $hwInventoryStationRepository
    ) {
        $this->hwInventoryStationRepository = $hwInventoryStationRepository;
    }

    public function getAll($onlyDisplayInSystem = false)
    {
        return $this->hwInventoryStationRepository->getAll($onlyDisplayInSystem);
    }

    public function getAllWithArea()
    {
        return $this->hwInventoryStationRepository->getAllWithArea();
    }
    
    public function getByStationCode(int $stationCode)
    {
        return $this->hwInventoryStationRepository->getByStationCode($stationCode);
    }

    public function paginate(array $options = [], $sort = "id", $order = 'ASC', int $page = 1, int $perPage = 15)
    {
        return $this->hwInventoryStationRepository->paginate($options, $sort, $order, $page, $perPage);
    }

    /**
     * Update Hydro Inventory Station data.
     * 
     * @param array $inventoryStations   - estacoes que vieram da API
     * 
     */
    public function update(array $inventoryStations)
    {
        foreach($inventoryStations as $station) {

            $inventoryStation = $this->hwInventoryStationRepository->getByStationCode($station['codigoestacao']);

            if(is_object($inventoryStation)) {
                $this->hwInventoryStationRepository->update($station['codigoestacao'], [
                    'station_code' => $station['codigoestacao'],
                    'station_name' => $station['Estacao_Nome'],
                    'station_uf' => $station['UF_Estacao'],
                    'station_uf_name' => $station['UF_Nome_Estacao'],
                    'basin_code' => $station['codigobacia'],
                    'basin_name' => $station['Bacia_Nome'],
                    'altitude' => $station['Altitude'],
                    'latitude' => $station['Latitude'],
                    'longitude' => $station['Longitude'],
                    'is_operational' => $station['Operando'],
                    'telemetry_station_type' => $station['Tipo_Estacao_Telemetrica'],
                    'water_quality_station_type' => $station['Tipo_Estacao_Qual_Agua'],
                    'responsible_code' => $station['Responsavel_Codigo'],
                    'responsible_acronym' => $station['Responsavel_Sigla'],
                    'responsible_unit_uf' => $station['Responsavel_Unidade_UF'],
                    'operator_code' => $station['Operadora_Codigo'],
                    'operator_abbreviation' => $station['Operadora_Sigla'],
                    'operator_sub_unit_status' => $station['Operadora_Sub_Unidade_UF'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $this->hwInventoryStationRepository->store([
                    'station_code' => $station['codigoestacao'],
                    'station_name' => $station['Estacao_Nome'],
                    'station_uf' => $station['UF_Estacao'],
                    'station_uf_name' => $station['UF_Nome_Estacao'],
                    'basin_code' => $station['codigobacia'],
                    'basin_name' => $station['Bacia_Nome'],
                    'altitude' => $station['Altitude'],
                    'latitude' => $station['Latitude'],
                    'longitude' => $station['Longitude'],
                    'is_operational' => $station['Operando'],
                    'telemetry_station_type' => $station['Tipo_Estacao_Telemetrica'],
                    'water_quality_station_type' => $station['Tipo_Estacao_Qual_Agua'],
                    'responsible_code' => $station['Responsavel_Codigo'],
                    'responsible_acronym' => $station['Responsavel_Sigla'],
                    'responsible_unit_uf' => $station['Responsavel_Unidade_UF'],
                    'operator_code' => $station['Operadora_Codigo'],
                    'operator_abbreviation' => $station['Operadora_Sigla'],
                    'operator_sub_unit_status' => $station['Operadora_Sub_Unidade_UF'],
                ]);
            }
        }
    }




    public function getOperationalStations()
    {
        return $this->hwInventoryStationRepository->getOperationalStations();
    }

    public function changeStatus(int $stationCode, array $data)
    {
        $this->hwInventoryStationRepository->update($stationCode, $data);
    }

    public function updateFilesId(int $stationCode, array $data)
    {
        $this->hwInventoryStationRepository->updateFilesId($stationCode, $data);
    }

    public function updateReferenceFlow(int $stationCode, array $data)
    {   
        $this->hwInventoryStationRepository->updateReferenceFlow($stationCode, $data);
    }

    public function getStatusActive()
    {
        return $this->hwInventoryStationRepository->getStatusActive();
    }

    public function getStationsByType(string $type = null, bool $active=true)
    {
        return $this->hwInventoryStationRepository->getStationsByType($type, $active);
    }

    public function deleteFiles(int $stationCode)
    {   
        $data = [
            'file_id_shapefile' => null,
            'file_id_geojson' => null,
        ];

        $this->hwInventoryStationRepository->deleteFiles($stationCode, $data);
    }
}
