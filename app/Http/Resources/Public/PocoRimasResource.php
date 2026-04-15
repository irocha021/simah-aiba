<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PocoRimasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_ponto'  => $this->id_ponto,
            'latitude'  => $this->latitude_d,
            'longitude' => $this->longitude,
        ];
    }
}
