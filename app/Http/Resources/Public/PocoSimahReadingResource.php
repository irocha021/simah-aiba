<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class PocoSimahReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'number'             => $this->number,
            'datetime_local'     => $this->datetime_local,
            'datetime_utc'       => $this->datetime_utc,
            'pd_bar'             => $this->pd_bar,
            'p1_bar'             => $this->p1_bar,
            'water_level_meters' => $this->water_level_meters,
            'p2_bar'             => $this->p2_bar,
            'tob1_celsius'       => $this->tob1_celsius,
            'tob2_celsius'       => $this->tob2_celsius,
        ];
    }
}
