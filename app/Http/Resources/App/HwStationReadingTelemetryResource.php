<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Resources\Json\ResourceCollection;

class HwStationReadingTelemetryResource extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'readings' => $this->collection->map(function ($reading) {
                return [
                    'station_code' => $reading->station_code,
                    'measurement_datetime' => $reading->measurement_datetime,
                    'adopted_rainfall' => $reading->adopted_rainfall,
                    'adopted_quota' => $reading->adopted_quota,
                    'adopted_flow' => $reading->adopted_flow,
                ];
            })
        ];
    }
}