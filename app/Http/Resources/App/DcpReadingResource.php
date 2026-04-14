<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DcpReadingResource extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'readings' => $this->collection
        ];
    }
}