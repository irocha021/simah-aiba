<?php

namespace App\Repositories;

use App\Models\HwEntity as Model;
use App\Repositories\Interfaces\HwEntityInterface;

class HwEntityRepository implements HwEntityInterface
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

    public function getEntitiesCode()
    {
        $dataDb = $this->model->orderBy('id' , 'ASC')->get();

        return $dataDb->pluck('entity_code')->toArray();
    }
}
 