<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HwFlowForecastResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'forecast_year'      => $this->forecast_year,
            'forecast_month'     => $this->forecast_month,
            'forecast_start_day' => $this->forecast_start_day,
            'predicted_flow'     => $this->predicted_flow,
            'minimum_flow'       => $this->minimum_flow,
            'alfa_pond'          => $this->alfa_pond,
            'q_noventa'          => $this->q_noventa,
            'vsup'               => $this->vsup,
        ];
    }
}
