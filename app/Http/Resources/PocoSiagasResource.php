<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PocoSiagasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // Identificação
            'ponto' => $this->ponto,
            'localizaca' => $this->localizaca,
            'data_insta' => $this->data_insta,
            
            // Coordenadas
            'cota_terre' => $this->cota_terre,
            'latitude_d' => $this->latitude_d,
            'longitude_' => $this->longitude_,
            'utme' => $this->utme,
            'utmn' => $this->utmn,
            
            // Localização geográfica
            'bacia' => $this->bacia,
            'municipio' => $this->municipio,
            'natureza' => $this->natureza,
            'nome' => $this->nome,
            'proprietar' => $this->proprietar,
            'subbacia' => $this->subbacia,
            'situacao' => $this->situacao,
            'uf' => $this->uf,
            'uso_agua' => $this->uso_agua,
            
            // Informações de perfuração
            'data_perfu' => $this->data_perfu,
            'metodo_per' => $this->metodo_per,
            'perfurador' => $this->perfurador,
            'diametro_b' => $this->diametro_b,
            'topo' => $this->topo,
            'base' => $this->base,
            'tipo_penet' => $this->tipo_penet,
            'condicao' => $this->condicao,
            'tipo_capta' => $this->tipo_capta,
            
            // Medições
            'data_medic' => $this->data_medic,
            'nivel_agua' => $this->nivel_agua,
            'vazao' => $this->vazao,
            'nivel_bomb' => $this->nivel_bomb,
            'profundida' => $this->profundida,
            'profundi_1' => $this->profundi_1,
            'tipo_forma' => $this->tipo_forma,
            
            // Testes
            'data_teste' => $this->data_teste,
            'tipo_teste' => $this->tipo_teste,
            'metodo_int' => $this->metodo_int,
            'surgencia' => $this->surgencia,
            'unidade_de' => $this->unidade_de,
            'nivel_dina' => $this->nivel_dina,
            'nivel_esta' => $this->nivel_esta,
            'vazao_espe' => $this->vazao_espe,
            'coeficient' => $this->coeficient,
            'vazao_livr' => $this->vazao_livr,
            'permeabili' => $this->permeabili,
            'transmissi' => $this->transmissi,
            'vazao_esta' => $this->vazao_esta,
            'tipo_bomba' => $this->tipo_bomba,
            
            // Análise de qualidade da água
            'data_anali' => $this->data_anali,
            'data_colet' => $this->data_colet,
            'condutivid' => $this->condutivid,
            'cor' => $this->cor,
            'odor' => $this->odor,
            'sabor' => $this->sabor,
            'temperatur' => $this->temperatur,
            'turbidez' => $this->turbidez,
            'solidos_se' => $this->solidos_se,
            'solidos_su' => $this->solidos_su,
            'aspecto_na' => $this->aspecto_na,
        ];
    }
}