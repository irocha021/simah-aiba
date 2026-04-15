<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PocoSiagasDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ponto'              => $this->ponto,
            'localizacao'        => $this->localizaca,
            'latitude'           => $this->latitude_d,
            'longitude'          => $this->longitude_,
            'utme'               => $this->utme,
            'utmn'               => $this->utmn,
            'bacia'              => $this->bacia,
            'municipio'          => $this->municipio,
            'natureza'           => $this->natureza,
            'nome'               => $this->nome,
            'subbacia'           => $this->subbacia,
            'uf'                 => $this->uf,
            'data_perfuracao'    => $this->data_perfu,
            'profundida'         => $this->profundida,
            'profundidade_total' => $this->profundi_1,
            'data_teste'         => $this->data_teste,
            'surgencia'          => $this->surgencia,
            'nivel_dinamico'     => $this->nivel_dina,
            'nivel_estatico'     => $this->nivel_esta,
            'vazao_especifica'   => $this->vazao_espe,
            'vazao_estabilizada' => $this->vazao_esta,
        ];
    }
}
