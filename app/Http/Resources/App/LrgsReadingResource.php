<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LrgsReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'reading_datetime'     => $this->reading_datetime?->format('Y-m-d H:i:s'),
            'water_level'          => $this->water_level,
            'flow'                 => $this->flow,
            'rain'                 => $this->rain,
            'water_temperature'    => $this->water_temperature,
            'atmospheric_pressure' => $this->atmospheric_pressure,
        ];
    }
}
