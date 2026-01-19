<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DbfImportController;
use App\Http\Controllers\CnarhUploadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\Jobs\HidroWeb\HidroFlowForecastController;
use App\Http\Controllers\Jobs\HidroWeb\HidroInfoAnaAdoptedTelemetricSeriesReadingController;
use App\Http\Controllers\Jobs\HidroWeb\HidroInventoryStationManagerController;
use App\Http\Controllers\Jobs\HidroWeb\HidroMonthlyTelemetricReadingController;
use App\Http\Controllers\Jobs\HidroWeb\HidroSerieQaReadingController;
use App\Http\Controllers\Jobs\Lrgs\ReadDcpMessagesController;

Route::prefix('jobs')->group(function () {
    //Grupo de rotas para a Hidroweb Anna
    Route::prefix('hidroweb')->group(function() {
        Route::get('/inventory-station', [HidroInventoryStationManagerController::class, 'index']);
        Route::get('/info-ana-adopted-telemetric-series-reading', [HidroInfoAnaAdoptedTelemetricSeriesReadingController::class, 'index']);
        Route::get('/readings/hidro-serie-qa', [HidroSerieQaReadingController::class, 'index']);
        Route::get('/readings/telemetric/monthly', [HidroMonthlyTelemetricReadingController::class, 'index']);
        Route::get('/flow-forecast', [HidroFlowForecastController::class, 'index']);
    });

    Route::prefix('lrgs')->group(function(){
        // LRGS DCP Messages - Processamento automático (período corrente)
        Route::get('/readings/dcp-messages', [ReadDcpMessagesController::class, 'retrieveMessages']);

        // LRGS DCP Messages - Reprocessamento manual (período específico)
        Route::get('/readings/dcp-messages/manual', [ReadDcpMessagesController::class, 'retrieveMessagesManual']);
        Route::post('/readings/dcp-messages/manual', [ReadDcpMessagesController::class, 'retrieveMessagesManual']);
    });
});

// Grupo de rotas para importação de DBF (SIAGAS e RIMAS)
Route::prefix('dbf-import')->group(function () {
    // Página de upload (GET)
    Route::get('/', [DbfImportController::class, 'index'])->name('dbf-import.index');

    // Upload e importação (recebe source como parâmetro)
    Route::post('/upload', [DbfImportController::class, 'upload'])->name('dbf-import.upload');

    // Listagem genérica (recebe source como query param)
    Route::get('/list', [DbfImportController::class, 'list'])->name('dbf-import.list');

    // Detalhes de um registro (recebe source como query param)
    Route::get('/show/{id}', [DbfImportController::class, 'show'])->name('dbf-import.show');

    // Deletar registro (recebe source como query param)
    Route::delete('/delete/{id}', [DbfImportController::class, 'delete'])->name('dbf-import.delete');
});

// Grupo de rotas para importação de CSV CNARH
Route::prefix('cnarh')->group(function () {
    Route::get('/', [CnarhUploadController::class, 'index'])->name('cnarh.index');
    Route::post('/upload', [CnarhUploadController::class, 'store'])->name('cnarh.upload');
});

Route::get('/', function () {
    return view('dashboard2');
});

// ROTA DE LOGIN
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// ROTAS DE CADASTRO
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// ROTAS DE PERFIL DO USUÁRIO (PROTEGIDAS POR AUTH)
Route::prefix('user')->group(function () {
    Route::get('/profile', [UserProfileController::class, 'showProfile'])->name('user.profile');
    Route::post('/profile', [UserProfileController::class, 'updateProfile'])->name('user.profile.update');
    
    Route::get('/password', [UserProfileController::class, 'showPassword'])->name('user.password');
    Route::post('/password', [UserProfileController::class, 'updatePassword'])->name('user.password.update');
});