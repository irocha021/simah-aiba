<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Public\LrgsController;
use App\Http\Controllers\Api\Public\PocoSiagasController;
use App\Http\Controllers\Api\Public\PocoRimasController;
use App\Http\Controllers\Api\Public\HwStationController;
use App\Http\Controllers\Api\Public\CnarhController;
use App\Http\Controllers\Api\Public\ApiKeyController;
use App\Http\Controllers\Api\Public\PocoSimahController;

// -----------------------------------------------------------------------
// Rotas protegidas por API Key + Rate Limit
// -----------------------------------------------------------------------
Route::middleware(['api.key', 'throttle:public-api'])->group(function () {

    // LRGS
    Route::prefix('lrgs')->group(function () {
        Route::get('/stations',                [LrgsController::class, 'stations'])
            ->name('public-api.lrgs.stations');
        Route::get('/{station_code}/readings', [LrgsController::class, 'readings'])
            ->name('public-api.lrgs.readings');
    });

    // Poços SIAGAS
    Route::prefix('siagas')->group(function () {
        Route::get('/wells',   [PocoSiagasController::class, 'wells'])
            ->name('public-api.siagas.wells');
        Route::get('/{ponto}', [PocoSiagasController::class, 'show'])
            ->name('public-api.siagas.show');
    });

    // Poços RIMAS
    Route::prefix('rimas')->group(function () {
        Route::get('/points',              [PocoRimasController::class, 'points'])
            ->name('public-api.rimas.points');
        Route::get('/{id_ponto}/readings', [PocoRimasController::class, 'readings'])
            ->name('public-api.rimas.readings');
    });

    // Estações ANA/HidroWeb
    Route::prefix('hidroweb')->group(function () {
        Route::get('/stations',                    [HwStationController::class, 'stations'])
            ->name('public-api.hidroweb.stations');
        Route::get('/{station_code}/telemetry',    [HwStationController::class, 'telemetryReadings'])
            ->name('public-api.hidroweb.telemetry');
        Route::get('/{station_code}/quality',      [HwStationController::class, 'qaReadings'])
            ->name('public-api.hidroweb.quality');
        Route::get('/{station_code}/forecast',     [HwStationController::class, 'forecastReadings'])
            ->name('public-api.hidroweb.forecast');
    });

    // CNARH
    Route::prefix('cnarh')->group(function () {
        Route::get('/',             [CnarhController::class, 'index'])
            ->name('public-api.cnarh.index');
        Route::get('/{cd_cnarh40}', [CnarhController::class, 'show'])
            ->name('public-api.cnarh.show');
    });

    // Poços SIMAH
    Route::prefix('poco-simah')->group(function () {
        Route::get('/stations',                [PocoSimahController::class, 'stations'])
            ->name('public-api.poco-simah.stations');
        Route::get('/{station_code}/readings', [PocoSimahController::class, 'readings'])
            ->name('public-api.poco-simah.readings');
    });


});
