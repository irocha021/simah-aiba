<?php

namespace App\Services\API_Hidroweb;

class HidrowebService extends BaseHidroWebService
{
    public function __construct(AuthService $authService)
    {
        parent::__construct($authService);
    }
    
    public function fetchHidroInventarioEstacoes(): array
    {
        return $this->get('EstacoesTelemetricas/HidroInventarioEstacoes/v1', ['Unidade Federativa' => 'BA']);
    }

    public function fetchHidroinfoanaSerieTelemetricaAdotada(array $params): array
    {
        return $this->get(
            'EstacoesTelemetricas/HidroinfoanaSerieTelemetricaAdotada/v1',
            $params
        );
    }

    public function fetchHidroSerieQA(array $params): array
    {
        return $this->get(
            'EstacoesTelemetricas/HidroSerieQA/v1',
            $params
        );
    }

    public function fetchHidroInventarioEstacaoByCode(int $stationCode): array
    {
        return $this->get('EstacoesTelemetricas/HidroInventarioEstacoes/v1', [
            'Unidade Federativa' => 'BA',
            'Código da Estação'  => $stationCode,
        ]);
    }

}
