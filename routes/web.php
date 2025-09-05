<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DcpMonitorController;
use App\Jobs\DcpSyncJob;

// Grupo de rotas para o DCP Monitor
Route::prefix('dcp-monitor')->group(function () {
    // Busca dados completos do DCP (agora com raw data automático)
    Route::get('/data/{dcpAddress?}', [DcpMonitorController::class, 'fetchDcpData'])
        ->name('dcp.data');
    
    // Busca apenas os links das mensagens
    Route::get('/message-links/{dcpAddress?}', [DcpMonitorController::class, 'fetchMessageLinks'])
        ->name('dcp.message-links');
    
    // Busca raw data de uma mensagem específica
    Route::get('/message-raw-data', [DcpMonitorController::class, 'fetchMessageRawData'])
        ->name('dcp.message-raw');
});

// Ou se preferir em formato de API RESTful:
Route::prefix('api/dcp')->group(function () {
    Route::get('/{dcpAddress}/data', [DcpMonitorController::class, 'fetchDcpData']);
    Route::get('/{dcpAddress}/links', [DcpMonitorController::class, 'fetchMessageLinks']);
});

Route::get('/test-dcp-sync', function () {
    DcpSyncJob::dispatchSync();
    
    return response()->json([
        'message' => 'Job executado - verifique o log/terminal'
    ]);
});