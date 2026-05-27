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
use App\Http\Controllers\Api\App\HwStationDrainageController;
use App\Http\Controllers\Api\App\PocoSimahController;

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
Route::get('/stations/pocos-simah', [StationController::class, 'getPocosSimah'])
    ->name('api.stations.pocos-simah');

Route::get('/pocos-simah/{station_code}/readings', [PocoSimahController::class, 'getReadings']);
Route::get('/pocos-simah/{station_code}/export', [PocoSimahController::class, 'exportReadings']);


Route::get('/pocos-rimas/{id_ponto}/readings', [PocoRimasController::class, 'getReadings']);
Route::get('/pocos-rimas/{id_ponto}/export', [PocoRimasController::class, 'exportReadings']);

Route::get('/pocos-siagas/{id_ponto}/readings', [PocoSiagasController::class, 'getReadings']);
Route::get('/hidroweb-qualidade-agua/{station_code}/readings', [HwStationReadingQaController::class, 'getReadings']);
Route::get('/hidroweb-qualidade-agua/{station_code}/export', [HwStationReadingQaController::class, 'exportReadings']);

Route::prefix('lrgs-client')->group(function () {
    Route::get('/stations',                [LrgsClientController::class, 'stations'])->name('api.lrgs.stations');
    Route::get('/{station_code}/readings', [LrgsClientController::class, 'getReadings'])->middleware('web');
    Route::get('/{station_code}/export',   [LrgsClientController::class, 'exportReadings'])->middleware('web');
});

Route::get('/hidroweb-telemetria/{station_code}/readings', [HwStationReadingTelemetryController::class, 'getReadings']);
Route::get('/hidroweb-telemetria/{station_code}/export', [HwStationReadingTelemetryController::class, 'exportReadings']);
Route::get('/hidroweb-telemetria/{station_code}/forecast', [App\Http\Controllers\Jobs\HidroWeb\HidroFlowForecastController::class, 'getForecastForStation']);
Route::get('/cnarh/{int_cd_cnarh40}/readings', [CnarhController::class, 'getReadings']);


Route::post('/auth/request-key', [App\Http\Controllers\Api\Public\ApiKeyController::class, 'store'])
    ->name('api.auth.request-key');

// Drenagens das estações HidroWeb (tiles prontos)
Route::get('/hw-station-drainages/ready', [HwStationDrainageController::class, 'ready'])
    ->name('api.hw-station-drainages.ready');
