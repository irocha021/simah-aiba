<?php

namespace App\Repositories;

use App\Models\HwStationReading as Model;
use App\Repositories\Interfaces\HwStationReadingInterface;
use App\Repositories\Presenters\PaginationPresenter;

class HwStationReadingRepository implements HwStationReadingInterface
{   
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function storeReadingsOfStation(array $data)
    {
        $this->model->insert($data);
    }
 
    public function getAll()
    {
        $dataDb = $this->model->orderBy('station_name' , 'ASC')->get();

        return $dataDb;
    }

    public function deleteByDate($date)
    {
        $startDate = $date . ' 00:00:00';
        $endDate = $date . ' 23:59:59';
        $this->model->whereBetween('measurement_datetime', [$startDate, $endDate])->delete();
    }

    public function getLastAdoptedFlowByStationCode($stationCode)
    {
        $dataDb = $this->model->where('station_code', $stationCode)
            ->whereNotNull('adopted_flow')
            ->orderBy('measurement_datetime', 'DESC')
            ->first();

        return $dataDb;
    }

    public function paginate(array $options = [], $sort = "id", $order = 'DESC', int $page = 1, int $perPage = 15) : PaginationPresenter
    {   
        $query = $this->model
            ->where(function ($query) use ($options) {
                if (isset($options['station_code'])) {
                    $query->where(function($q) use ($options) {
                        $q->where('station_code','=', $options['station_code']);                    
                    });
                }

                if (isset($options['data_de']) && isset($options['data_ate'])) {
                    $query->where(function($q) use ($options) {
                        $startDate = $options['data_de'] . ' 00:00:00';
                        $endDate   = $options['data_ate'] . ' 23:59:59';
                        $q->whereBetween('measurement_datetime', [$startDate, $endDate]);
                    });
                }
             });

        $query = $query->orderBy('measurement_datetime', $order);

        $dataDb = $query->paginate($perPage, ['*'], 'page', $page);

        return new PaginationPresenter($dataDb);
    }

}
 