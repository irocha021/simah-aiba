<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HwStationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->stationData;
        $hasForecast = $data &&
            $data->alfa_pond !== null &&
            $data->q_noventa !== null &&
            $data->vsup !== null;

        return [
            'station_code'      => $this->station_code,
            'station_name'      => $this->station_name,
            'latitude'          => $this->latitude,
            'longitude'         => $this->longitude,
            'is_telemetry'      => (bool) $this->telemetry_station_type,
            'is_water_quality'  => (bool) $this->water_quality_station_type,
            'forecast'          => $hasForecast ? [
                'alfa_pond' => $data->alfa_pond,
                'q_noventa' => $data->q_noventa,
                'vsup'      => $data->vsup,
            ] : null,
        ];
    }
}
