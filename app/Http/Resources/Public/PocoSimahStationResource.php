<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class PocoSimahStationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'station_code' => $this->station_code,
            'name'         => $this->name,
            'latitude'     => $this->latitude,
            'longitude'    => $this->longitude,
            'depth'        => $this->depth,
            'ativa'        => $this->ativa,
        ];
    }
}
