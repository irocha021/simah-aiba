<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DbfImportController;
use App\Http\Controllers\CnarhUploadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\GeobahiaImportController;
use App\Http\Controllers\HwInventoryStationController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\Jobs\HidroWeb\HidroFlowForecastController;
use App\Http\Controllers\Jobs\HidroWeb\HidroInfoAnaAdoptedTelemetricSeriesReadingController;
use App\Http\Controllers\Jobs\HidroWeb\HidroInventoryStationManagerController;
use App\Http\Controllers\Jobs\HidroWeb\HidroMonthlyTelemetricReadingController;
use App\Http\Controllers\Jobs\HidroWeb\HidroSerieQaReadingController;
use App\Http\Controllers\Jobs\Lrgs\ReadDcpMessagesController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\LrgsStationController;


// ============================
// ROTAS PUBLICAS (sem auth)
// ============================

//Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/password/forgot', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])->name('password.update');

// Dashboard
Route::middleware(['force.password.change'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dash2', [DashboardController::class, 'index2'])->name('dashboard2');
});


// ============================
// ROTAS DE JOBS (sem auth - usadas por cron)
// ============================
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

Route::prefix('geobahia')->group(function(){
    Route::get('/import/{layer}/{minZoom?}/{maxZoom?}/{opacity?}', [GeobahiaImportController::class, 'import']);
    Route::get('/import-all', [GeobahiaImportController::class, 'importAll']);
});

Route::get('/api-key', [App\Http\Controllers\Api\Public\ApiKeyController::class, 'showForm'])->name('api-key.form');
Route::post('/api-key', [App\Http\Controllers\Api\Public\ApiKeyController::class, 'requestKey'])->name('api-key.request');

// ============================
// ROTAS AUTENTICADAS
// ============================
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Perfil e senha (sem force.password.change para nao criar loop)
    Route::get('/user/password', [UserProfileController::class, 'showPassword'])->name('user.password');
    Route::post('/user/password', [UserProfileController::class, 'updatePassword'])->name('user.password.update');

    // Rotas protegidas com force.password.change
    Route::middleware(['force.password.change'])->group(function () {

        Route::get('/users/list', [UserManagementController::class, 'list'])->name('users.list');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');


        // Perfil
        Route::get('/user/profile', [UserProfileController::class, 'showProfile'])->name('user.profile');
        Route::post('/user/profile', [UserProfileController::class, 'updateProfile'])->name('user.profile.update');

        // Cadastro de admins
        Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

        // Importacoes
        Route::prefix('dbf-import')->group(function () {
            Route::get('/', [DbfImportController::class, 'index'])->name('dbf-import.index');
            Route::post('/upload', [DbfImportController::class, 'upload'])->name('dbf-import.upload');
            Route::get('/list', [DbfImportController::class, 'list'])->name('dbf-import.list');
            Route::get('/show/{id}', [DbfImportController::class, 'show'])->name('dbf-import.show');
            Route::delete('/delete/{id}', [DbfImportController::class, 'delete'])->name('dbf-import.delete');
        });

        Route::prefix('cnarh')->group(function () {
            Route::get('/', [CnarhUploadController::class, 'index'])->name('cnarh.index');
            Route::post('/upload', [CnarhUploadController::class, 'store'])->name('cnarh.upload');
        });

        Route::prefix('lrgs-stations')->group(function () {
            Route::get('/',           [LrgsStationController::class, 'index'])->name('lrgs-stations.index');
            Route::get('/create',     [LrgsStationController::class, 'create'])->name('lrgs-stations.create');
            Route::post('/',          [LrgsStationController::class, 'store'])->name('lrgs-stations.store');
            Route::get('/{id}/edit',  [LrgsStationController::class, 'edit'])->name('lrgs-stations.edit');
            Route::post('/{id}',      [LrgsStationController::class, 'update'])->name('lrgs-stations.update');
            Route::delete('/{id}',    [LrgsStationController::class, 'destroy'])->name('lrgs-stations.destroy');
            Route::post('/{id}/rating-curves',                [LrgsStationController::class, 'storeCurve'])->name('lrgs-stations.rating-curves.store');
            Route::post('/{id}/rating-curves/{curveId}',     [LrgsStationController::class, 'updateCurve'])->name('lrgs-stations.rating-curves.update');
            Route::delete('/{id}/rating-curves/{curveId}',   [LrgsStationController::class, 'destroyCurve'])->name('lrgs-stations.rating-curves.destroy');
        });


        Route::prefix('hw-inventory-stations')->group(function () {
            Route::get('/',            [HwInventoryStationController::class, 'index'])->name('hw-inventory-stations.index');
            Route::get('/create',      [HwInventoryStationController::class, 'create'])->name('hw-inventory-stations.create');
            Route::post('/',           [HwInventoryStationController::class, 'store'])->name('hw-inventory-stations.store');
            Route::get('/{code}/edit', [HwInventoryStationController::class, 'edit'])->name('hw-inventory-stations.edit');
            Route::post('/{code}',     [HwInventoryStationController::class, 'update'])->name('hw-inventory-stations.update');
            Route::delete('/{code}',   [HwInventoryStationController::class, 'destroy'])->name('hw-inventory-stations.destroy');
        });
    });
});