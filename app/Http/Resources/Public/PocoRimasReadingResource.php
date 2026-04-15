<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PocoRimasReadingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'numero_medicao' => $this->numero_de,
            'data'           => $this->data_da_me,
            'hora'           => substr($this->hora_da_me ?? '', 0, 8),
            'nivel_agua'     => $this->nivel_da_a,
            'observacao'     => $this->field_8 ?: null,
        ];
    }
}
