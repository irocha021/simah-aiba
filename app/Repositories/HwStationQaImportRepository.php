<?php

namespace App\Repositories;

use App\Models\HwStationQaImport as Model;
use App\Repositories\Interfaces\HwStationQaImportInterface;

class HwStationQaImportRepository implements HwStationQaImportInterface
{   
    protected $model; 

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        $dataDb = $this->model->orderBy('id' , 'ASC')->get();

        return $dataDb;
    }

    public function store($data)
    {   
        $this->model->query()->delete();

        $hwEntity = $this->model->insert($data);

        return $hwEntity;
    }
}
 