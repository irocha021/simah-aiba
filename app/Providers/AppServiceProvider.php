<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\DcpStationRepositoryInterface;
use App\Repositories\DcpStationRepository;
use App\Repositories\Interfaces\DcpStationTransmissionRepositoryInterface;
use App\Repositories\DcpStationTransmissionRepository;
use App\Repositories\Interfaces\DcpTransmissionRawDataRepositoryInterface;
use App\Repositories\DcpTransmissionRawDataRepository;
use App\Repositories\Interfaces\DcpFailureCodeRepositoryInterface;
use App\Repositories\DcpFailureCodeRepository;
use App\Repositories\DcpSyncLogRepository;
use App\Repositories\Interfaces\DcpSyncLogRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DcpStationRepositoryInterface::class, DcpStationRepository::class);
        $this->app->bind(DcpStationTransmissionRepositoryInterface::class, DcpStationTransmissionRepository::class);
        $this->app->bind(DcpTransmissionRawDataRepositoryInterface::class, DcpTransmissionRawDataRepository::class);
        $this->app->bind(DcpFailureCodeRepositoryInterface::class, DcpFailureCodeRepository::class);
        $this->app->bind(DcpSyncLogRepositoryInterface::class, DcpSyncLogRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
