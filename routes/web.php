<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DcpMonitorController;
use App\Http\Controllers\DbfZipController;
use App\Http\Controllers\DbfReaderController;
use App\Http\Controllers\DbfImportController;
use App\Http\Controllers\Jobs\HidroWeb\HidroInfoAnaAdoptedTelemetricSeriesReadingController;
use App\Http\Controllers\Jobs\HidroWeb\HidroInventoryStationManagerController;
use App\Http\Controllers\Jobs\HidroWeb\HidroSerieQaReadingController;
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


//Grupo de rotas para a Hidroweb Anna
Route::prefix('jobs')->group(function () {
    Route::get('/hidroweb/inventory-station', [HidroInventoryStationManagerController::class, 'index']);
    Route::get('/hidroweb/info-ana-adopted-telemetric-series-reading', [HidroInfoAnaAdoptedTelemetricSeriesReadingController::class, 'index']);
    Route::get('/hidroweb/readings/hidro-serie-qa', [HidroSerieQaReadingController::class, 'index']);
});

// Grupo de rotas para extração de DBF do ZIP
Route::prefix('dbf-zip')->group(function () {
    // Extrai o arquivo DBF do ZIP
    Route::get('/extract', [DbfZipController::class, 'extract'])
        ->name('dbf-zip.extract');

    // Lista todos os arquivos dentro do ZIP
    Route::get('/list', [DbfZipController::class, 'listFiles'])
        ->name('dbf-zip.list');

    // Remove o arquivo DBF extraído do storage temporário
    Route::delete('/clean', [DbfZipController::class, 'clean'])
        ->name('dbf-zip.clean');
});

// Grupo de rotas para leitura de DBF
Route::prefix('dbf-reader')->group(function () {
    // Informações gerais do DBF (colunas + total)
    Route::get('/info', [DbfReaderController::class, 'info'])
        ->name('dbf-reader.info');

    // Lista todas as colunas do DBF
    Route::get('/columns', [DbfReaderController::class, 'columns'])
        ->name('dbf-reader.columns');

    // Total de registros
    Route::get('/total', [DbfReaderController::class, 'total'])
        ->name('dbf-reader.total');

    // Primeiros N registros (teste)
    Route::get('/first', [DbfReaderController::class, 'first'])
        ->name('dbf-reader.first');

    // Registros com paginação
    Route::get('/records', [DbfReaderController::class, 'records'])
        ->name('dbf-reader.records');

    // Buscar registros por valor
    Route::get('/search', [DbfReaderController::class, 'search'])
        ->name('dbf-reader.search');
});

// Grupo de rotas para importação de DBF (SIAGAS e RIMAS)
Route::prefix('dbf-import')->group(function () {
    // Página de upload (GET)
    Route::get('/', [DbfImportController::class, 'index'])
        ->name('dbf-import.index');

    // Upload e importação (recebe source como parâmetro)
    Route::post('/upload', [DbfImportController::class, 'upload'])
        ->name('dbf-import.upload');

    // Listagem genérica (recebe source como query param)
    Route::get('/list', [DbfImportController::class, 'list'])
        ->name('dbf-import.list');

    // Detalhes de um registro (recebe source como query param)
    Route::get('/show/{id}', [DbfImportController::class, 'show'])
        ->name('dbf-import.show');

    // Deletar registro (recebe source como query param)
    Route::delete('/delete/{id}', [DbfImportController::class, 'delete'])
        ->name('dbf-import.delete');
});



Route::get('/test-dcp-sync', function () {
    DcpSyncJob::dispatchSync();
    
    return response()->json([
        'message' => 'Job executado - verifique o log/terminal'
    ]);
});