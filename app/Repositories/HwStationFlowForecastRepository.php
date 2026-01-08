<?php

namespace App\Repositories;

use App\Models\HwStationFlowForecast as Model;
use App\Repositories\Interfaces\HwStationFlowForecastInterface;
use App\Repositories\Presenters\PaginationPresenter;
use Illuminate\Support\Facades\Log;

class HwStationFlowForecastRepository implements HwStationFlowForecastInterface
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function store(array $data)
    {
        return $this->model->create($data);
    }

    public function updateOrCreate(array $conditions, array $data)
    {
        return $this->model->updateOrCreate($conditions, $data);
    }

    public function getByStationAndPeriod(int $stationCode, int $year, int $month)
    {
        return $this->model
            ->where('station_code', $stationCode)
            ->where('forecast_year', $year)
            ->where('forecast_month', $month)
            ->first();
    }

    public function getAll()
    {
        return $this->model->orderBy('forecast_year', 'DESC')
                           ->orderBy('forecast_month', 'DESC')
                           ->get();
    }

    public function paginate(array $options = [], $sort = "id", $order = 'DESC', int $page = 1, int $perPage = 15): PaginationPresenter
    {
        $query = $this->model->where(function ($query) use ($options) {
            if (isset($options['station_code'])) {
                $query->where('station_code', '=', $options['station_code']);
            }
            if (isset($options['forecast_year'])) {
                $query->where('forecast_year', '=', $options['forecast_year']);
            }
            if (isset($options['forecast_month'])) {
                $query->where('forecast_month', '=', $options['forecast_month']);
            }
        });

        $query = $query->orderBy($sort, $order);
        $dataDb = $query->paginate($perPage, ['*'], 'page', $page);

        return new PaginationPresenter($dataDb);
    }

    public function deleteByYearMonth(int $year, int $month)
    {
        return $this->model
            ->where('forecast_year', $year)
            ->where('forecast_month', $month)
            ->delete();
    }

    public function deleteByStationYearMonth(int $stationCode, int $year, int $month)
    {
        // Soft delete - preenche deleted_at
        return $this->model
            ->where('station_code', $stationCode)
            ->where('forecast_year', $year)
            ->where('forecast_month', $month)
            ->delete(); // Soft delete normal
    }
}
