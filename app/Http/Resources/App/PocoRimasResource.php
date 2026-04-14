<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PocoRimasResource extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'readings' => $this->collection->map(function ($reading) {
                return [
                    'numero_de' => $reading->numero_de,
                    'data_da_me' => $reading->data_da_me,
                    'hora_da_me' => $reading->hora_da_me,
                    'nivel_da_a' => $reading->nivel_da_a,
                    'field_8' => $reading->field_8,
                ];
            })
        ];
    }
}