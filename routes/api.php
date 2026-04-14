<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\App\StationController;
use App\Http\Controllers\Api\App\PocoRimasController;
use App\Http\Controllers\Api\App\PocoSiagasController;
use App\Http\Controllers\Api\App\HwStationReadingQaController;
use App\Http\Controllers\Api\App\HwStationReadingTelemetryController;
use App\Http\Controllers\Api\App\LrgsClientController;
use App\Http\Controllers\Api\App\CnarhController;

// Rotas de API com middleware 'api' aplicado automaticamente
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Stations API for Map Visualization
Route::prefix('stations')->group(function () {
    Route::get('/', [StationController::class, 'index'])
        ->name('api.stations.index');
});

// Stations por fonte (lazy loading no mapa)
Route::get('/stations/cnarh', [StationController::class, 'getCnarh'])
    ->name('api.stations.cnarh');
Route::get('/stations/pocos-rimas', [StationController::class, 'getPocosRimas'])
    ->name('api.stations.pocos-rimas');
Route::get('/stations/pocos-siagas', [StationController::class, 'getPocosSiagas'])
    ->name('api.stations.pocos-siagas');
Route::get('/stations/hidroweb-telemetria', [StationController::class, 'getHidrowebTelemetria'])
    ->name('api.stations.hidroweb-telemetria');
Route::get('/stations/hidroweb-qualidade-agua', [StationController::class, 'getHidrowebQualidadeAgua'])
    ->name('api.stations.hidroweb-qualidade-agua');
Route::get('/stations/hidroweb-telemetria-previsao', [StationController::class, 'getHidrowebTelemetriaPrevisao'])
    ->name('api.stations.hidroweb-telemetria-previsao');
Route::get('/stations/lrgs-client', [StationController::class, 'getLrgsClient'])
    ->name('api.stations.lrgs-client');


Route::get('/pocos-rimas/{id_ponto}/readings', [PocoRimasController::class, 'getReadings']);
Route::get('/pocos-siagas/{id_ponto}/readings', [PocoSiagasController::class, 'getReadings']);
Route::get('/hidroweb-qualidade-agua/{station_code}/readings', [HwStationReadingQaController::class, 'getReadings']);

Route::prefix('lrgs-client')->group(function () {
    Route::get('/stations',                [LrgsClientController::class, 'stations'])->name('api.lrgs.stations');
    Route::get('/{station_code}/readings', [LrgsClientController::class, 'getReadings'])->middleware('web');
    Route::get('/{station_code}/export',   [LrgsClientController::class, 'exportReadings'])->middleware('web');
});

Route::get('/hidroweb-telemetria/{station_code}/readings', [HwStationReadingTelemetryController::class, 'getReadings']);
Route::get('/hidroweb-telemetria/{station_code}/forecast', [App\Http\Controllers\Jobs\HidroWeb\HidroFlowForecastController::class, 'getForecastForStation']);
Route::get('/cnarh/{int_cd_cnarh40}/readings', [CnarhController::class, 'getReadings']);


Route::post('/auth/request-key', [App\Http\Controllers\Api\Public\ApiKeyController::class, 'store'])
    ->name('api.auth.request-key');
