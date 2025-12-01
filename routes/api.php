<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StationController;

// Rotas de API com middleware 'api' aplicado automaticamente
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Unified Stations API for Map Visualization
Route::prefix('stations')->group(function () {
    Route::get('/', [StationController::class, 'index'])
        ->name('api.stations.index');
});