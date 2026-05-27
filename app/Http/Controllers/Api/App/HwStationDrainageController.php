<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Models\HwStationDrainageLayer;
use Illuminate\Http\JsonResponse;

class HwStationDrainageController extends Controller
{
    /**
     * Retorna as áreas de drenagem das estações HidroWeb com tiles prontos.
     * Consumido pelo mapa principal (layer-control.js) quando o usuário ativa
     * algum toggle HidroWeb (telemetria ou qualidade da água).
     */
    public function ready(): JsonResponse
    {
        $drainages = HwStationDrainageLayer::where('status', 'ready')
            ->whereNotNull('url_pattern')
            ->get([
                'station_code',
                'url_pattern',
                'min_zoom',
                'max_zoom',
                'bounds_latlng_json',
            ]);

        return response()->json(['data' => $drainages]);
    }
}
