<?php

namespace App\Http\Resources;

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