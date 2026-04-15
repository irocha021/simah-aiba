<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\ResourceCollection;

class HwStationReadingQaResource extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'readings' => $this->collection
        ];
    }
}