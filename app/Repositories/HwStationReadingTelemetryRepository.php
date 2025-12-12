<?php

namespace App\Repositories;

use App\Models\HwStationReadingTelemetry as Model;
use App\Repositories\Interfaces\HwStationReadingTelemetryInterface;
use App\Repositories\Presenters\PaginationPresenter;
use Illuminate\Support\Facades\Log;
use Exception;

class HwStationReadingTelemetryRepository implements HwStationReadingTelemetryInterface
{   
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function storeReadingsOfStation(array $data)
    {
        try {
           
            $result = $this->model->insert($data);

            if (!$result) {
                Log::error('Falha ao inserir readings - insert retornou false', [
                    'data_count' => count($data),
                    'first_item' => !empty($data) ? $data[0] : null
                ]);
                throw new Exception('Insert operation returned false');
            }

            Log::info('Readings inseridas com sucesso', [
                'total_records' => count($data)
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('ERRO ao inserir readings de estação', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data_count' => count($data),
                'first_item' => !empty($data) ? $data[0] : null,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
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

    public function getReadingsByStationCode(string $stationCode, int $limit = 50)
    {
        return $this->model->where('station_code', $stationCode)
            ->orderBy('measurement_datetime', 'desc')
            ->limit($limit)
            ->get();
    }

}
 