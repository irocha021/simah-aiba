<?php

namespace App\Repositories;

use App\Models\HwInventoryStation as Model;
use App\Repositories\Interfaces\HwInventoryStationInterface;
use App\Repositories\Presenters\PaginationPresenter;

class HwInventoryStationRepository implements HwInventoryStationInterface
{   
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function store(array $data)
    {
        $this->model->create($data);
    }

    public function update(int $id, array $data)
    {   
        $station = $this->model->where('station_code', $id)->first();
        $station->update($data);
    }

    public function getByStationCode(int $stationCode)
    {
        $dataDb = $this->model->where('station_code', $stationCode)->first();

        return $dataDb;
    }

    public function getAll($onlyDisplayInSystem = false)
    {
        if ($onlyDisplayInSystem) {
            $dataDb = $this->model->with('stationData')->where('status', 1)->get();
        } else {
            $dataDb = $this->model->with('stationData')->get();
        }

        return $dataDb;
    }

    public function getAllWithArea()
    {
        $dataDb = $this->model
                    ->whereNotNull('file_id_geojson')
                    ->where('status', 1)
                    ->get();

        return $dataDb;
    }

    public function getOperationalStations()
    {
        $dataDb = $this->model->where('is_operational', 1)->get();

        return $dataDb;
    }

    public function paginate(array $options = [], $sort = "id", $order = 'DESC', int $page = 1, int $perPage = 15): PaginationPresenter 
    {
        $query = $this->model
            // SEARCH
            ->when(!empty($options['search']), function($q) use ($options) {
                $term = $options['search'];
                $q->where(function($q2) use ($term) {
                    $q2->where('station_code', 'ilike', "%{$term}%")
                    ->orWhere('station_name', 'ilike', "%{$term}%");
                });
            })
            // STATUS
            ->when(isset($options['status']), function($q) use ($options) {
                $q->where('status', (bool) $options['status']);
            })
            // SHAPEFILE
            ->when(isset($options['shapefile']), function($q) use ($options) {
                if ($options['shapefile'] === 'with') {
                    $q->whereNotNull('file_id_geojson');
                } elseif ($options['shapefile'] === 'without') {
                    $q->whereNull('file_id_geojson');
                }
            })
            ->with(['geojson', 'shapefile'])
            ->orderBy($sort, $order);

        $dataDb = $query->paginate($perPage, ['*'], 'page', $page);

        return new PaginationPresenter($dataDb);
    }

    public function changeStatus(int $stationCode, array $data)
    {
        $station = $this->model->where('station_code', $stationCode)->first();
        $station->update($data);
    }

    public function updateFilesId(int $stationCode, array $data)
    {   
        $station = $this->model->where('station_code', $stationCode)->first();
        $station->update($data);
    }

    public function updateReferenceFlow(int $stationCode, array $data)
    {     
        $station = $this->model->where('station_code', $stationCode)->first();
        $station->update($data);
    }

    public function getStatusActive()
    {
        $dataDb = $this->model->where('status', 1)->get();

        return $dataDb;
    }

    /**
     * Get stations by type and status.
     * @param string|null $type ['telemetry', 'water_quality']
     * @param bool $active
     */
    public function getStationsByType(string $type = null, bool $active=true)
    {
        $query = $this->model->newQuery()->with('stationData');

        if ($type === 'telemetry') {
            $query->where('telemetry_station_type', 1);
        } elseif ($type === 'water_quality') {
            $query->where('water_quality_station_type', 1);
        } elseif ($type === 'both') {
            $query->where('telemetry_station_type', 1)
                  ->where('water_quality_station_type', 1);
        } elseif ($type === 'telemetry_forecast') {
            $query->whereHas('stationData', function ($q) {
                $q->whereNotNull('alfa_pond')
                  ->whereNotNull('q_noventa')
                  ->whereNotNull('vsup');
            });
        }

        if ($active) {
            $query->where('status', 1);
        }
        
        return $query->get();
    }

    public function deleteFiles(int $stationCode, array $data)
    {
        $station = $this->model->where('station_code', $stationCode)->first();
        $station->update($data);
    }

    public function getAllWithCoordinates()
    {
        // Retorna TODAS as estações com coordenadas sem JOIN
        return $this->model
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
    }

    public function getTelemetryStationsWithCoordinates()
    {
        // Estações que têm dados de telemetria
        return $this->model
            ->select('hw_inventory_stations.*')
            ->join('hw_station_telemetry_import', 'hw_inventory_stations.station_code', '=', 'hw_station_telemetry_import.station_code')
            ->whereNotNull('hw_inventory_stations.latitude')
            ->whereNotNull('hw_inventory_stations.longitude')
            ->distinct()
            ->get();
    }

    public function getTelemetryStationsWithForecastCoordinates()
    {
        // Estações que têm dados de previsão de vazão
        return $this->model
            ->select('hw_inventory_stations.*')
            ->join('hw_station_flow_forecasts', 'hw_inventory_stations.station_code', '=', 'hw_station_flow_forecasts.station_code')
            ->whereNotNull('hw_inventory_stations.latitude')
            ->whereNotNull('hw_inventory_stations.longitude')
            ->distinct()
            ->get();
    }

    public function getQualityStationsWithCoordinates()
    {
        // Estações que têm dados de qualidade da água
        return $this->model
            ->select('hw_inventory_stations.*')
            ->join('hw_station_qa_import', 'hw_inventory_stations.station_code', '=', 'hw_station_qa_import.station_code')
            ->whereNotNull('hw_inventory_stations.latitude')
            ->whereNotNull('hw_inventory_stations.longitude')
            ->distinct()
            ->get();
    }

    public function destroy(int $stationCode): void
    {
        $station = $this->model->where('station_code', $stationCode)->first();
        if ($station) {
            $station->delete();
        }
    }

    public function getByStationCodeWithTrashed(int $stationCode)
    {
        return $this->model->withTrashed()->where('station_code', $stationCode)->first();
    }

    public function restoreAndUpdate(int $stationCode, array $data): void
    {
        $station = $this->model->withTrashed()->where('station_code', $stationCode)->first();
        $station->restore();
        $station->update($data);
    }


}
 