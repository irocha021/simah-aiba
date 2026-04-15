<?php

namespace App\Http\Resources\App;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LrgsStationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'dcp_address'   => $this->dcp_address,
            'station_label' => $this->station_label,
            'latitude'      => $this->latitude ? (float) $this->latitude : null,
            'longitude'     => $this->longitude ? (float) $this->longitude : null,
            'is_active'     => $this->is_active,
        ];
    }
}
