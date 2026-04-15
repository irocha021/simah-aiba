<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HwTelemetryReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'measurement_datetime' => $this->measurement_datetime,
            'adopted_rainfall'     => $this->adopted_rainfall,
            'adopted_quota'        => $this->adopted_quota,
            'adopted_flow'         => $this->adopted_flow,
        ];
    }
}
