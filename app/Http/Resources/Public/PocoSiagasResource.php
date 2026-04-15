<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PocoSiagasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ponto'       => $this->ponto,
            'localizacao' => $this->localizaca,
            'latitude'    => $this->latitude_d,
            'longitude'   => $this->longitude_,
        ];
    }
}
