<?php

namespace App\Repositories;

use App\Models\HwStationReadingQa as Model;
use App\Repositories\Interfaces\HwStationReadingQaInterface;
use App\Repositories\Presenters\PaginationPresenter;
use Log;

class HwStationReadingQaRepository implements HwStationReadingQaInterface
{   
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function storeReadingsOfStation(array $data)
    {
        try {
            // Remove o dd() que estava aqui
            
            // Log para ver quantos registros estão tentando inserir
            Log::info('Tentando inserir ' . count($data) . ' registros de QA');
            
            // Log do primeiro registro para verificar a estrutura
            if (!empty($data)) {
                Log::info('Primeiro registro:', $data[0]);
            }
            
            // Tenta inserir
            $result = $this->model->insert($data);
            
            // Log do resultado
            Log::info('Inserção concluída. Resultado: ' . ($result ? 'sucesso' : 'falha'));
            
            return $result;
            
        } catch (\Exception $e) {
            // Log do erro
            Log::error('Erro ao inserir dados de QA: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Se quiser debugar, use dd() aqui para ver o erro
            dd([
                'erro' => $e->getMessage(),
                'dados' => $data,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function getExistingRecords($stationCode, $dateInitial, $dateFinal)
    {
        return $this->model
            ->where('station_code', $stationCode)
            ->whereBetween('data_hora_dado', [$dateInitial . ' 00:00:00', $dateFinal . ' 23:59:59'])
            ->get()
            ->keyBy(function ($item) {
                // Cria uma chave única combinando station_code e data_hora_dado
                return $item->station_code . '_' . $item->data_hora_dado;
            });
    }

    public function insertSingle(array $data)
    {
        return $this->model->create($data);
    }

    public function updateSingle($stationCode, $dataHoraDado, array $data)
    {
        return $this->model
            ->where('station_code', $stationCode)
            ->where('data_hora_dado', $dataHoraDado)
            ->update($data);
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
            ->orderBy('data_hora_dado', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getReadingsByStationCodeAndDateRange(string $stationCode, ?string $dateFrom, ?string $dateTo)
    {
        $query = $this->model->where('station_code', $stationCode)
            ->orderBy('data_hora_dado', 'desc');

        if ($dateFrom) {
            $query->where('data_hora_dado', '>=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo) {
            $query->where('data_hora_dado', '<=', $dateTo . ' 23:59:59');
        }

        return $query->get();
    }

    public function cursorByStationCodeAndDateRange(string $stationCode, ?string $dateFrom, ?string $dateTo): \Generator
    {
        $query = $this->model->where('station_code', $stationCode)
            ->when($dateFrom, fn($q) => $q->where('data_hora_dado', '>=', $dateFrom . ' 00:00:00'))
            ->when($dateTo,   fn($q) => $q->where('data_hora_dado', '<=', $dateTo   . ' 23:59:59'))
            ->orderBy('data_hora_dado', 'desc');

        if (!$dateFrom && !$dateTo) {
            $query->limit(50);
        }

        foreach ($query->cursor() as $record) {
            yield $record;
        }
    }

    public function paginateReadings(string $stationCode, ?string $dateFrom, ?string $dateTo, int $page, int $perPage)
    {
        return $this->model->where('station_code', $stationCode)
            ->when($dateFrom, fn($q) => $q->where('data_hora_dado', '>=', $dateFrom . ' 00:00:00'))
            ->when($dateTo,   fn($q) => $q->where('data_hora_dado', '<=', $dateTo   . ' 23:59:59'))
            ->orderBy('data_hora_dado', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

}
 