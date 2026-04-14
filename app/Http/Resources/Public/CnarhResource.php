<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CnarhResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cd_cnarh40'      => $this->int_cd_cnarh40,
            'nome'            => $this->emp_nm_empreendimento,
            'latitude'        => $this->int_nu_latitude,
            'longitude'       => $this->int_nu_longitude,
        ];
    }
}
