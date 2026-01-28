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
use App\Repositories\HwEntityRepository;
use App\Repositories\HwInventoryStationRepository;
use App\Repositories\HwStationQaImportRepository;
use App\Repositories\HwStationReadingQaRepository;
use App\Repositories\HwStationReadingTelemetryRepository;
use App\Repositories\Interfaces\DcpSyncLogRepositoryInterface;
use App\Repositories\Interfaces\DcpReadingRepositoryInterface;
use App\Repositories\DcpReadingRepository;
use App\Repositories\Interfaces\HwEntityInterface;
use App\Repositories\Interfaces\HwInventoryStationInterface;
use App\Repositories\Interfaces\HwStationQaImportInterface;
use App\Repositories\Interfaces\HwStationReadingTelemetryInterface;
use App\Repositories\Interfaces\HwStationReadingQaInterface;
use App\Repositories\Interfaces\JobStatusInterface;
use App\Repositories\JobStatusRepository;
use App\Repositories\Interfaces\PocoSiagasRepositoryInterface;
use App\Repositories\PocoSiagasRepository;
use App\Repositories\Interfaces\PocoRimasRepositoryInterface;
use App\Repositories\PocoRimasRepository;
use App\Repositories\Interfaces\CnarhRepositoryInterface;
use App\Repositories\CnarhRepository;
use App\Repositories\HwStationTelemetryImportRepository;
use App\Repositories\Interfaces\HwStationTelemetryImportInterface;
use App\Repositories\Interfaces\MapLayerRepositoryInterface;
use App\Repositories\MapLayerRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DcpStationRepositoryInterface::class, DcpStationRepository::class);
        $this->app->bind(DcpFailureCodeRepositoryInterface::class, DcpFailureCodeRepository::class);
        $this->app->bind(DcpSyncLogRepositoryInterface::class, DcpSyncLogRepository::class);
        $this->app->bind(DcpReadingRepositoryInterface::class, DcpReadingRepository::class);

        // Bindings for HidroWeb repositories
        $this->app->bind(HwInventoryStationInterface::class, HwInventoryStationRepository::class);
        $this->app->bind(HwEntityInterface::class, HwEntityRepository::class);
        $this->app->bind(HwStationReadingTelemetryInterface::class, HwStationReadingTelemetryRepository::class);
        $this->app->bind(HwStationReadingQaInterface::class, HwStationReadingQaRepository::class);
        $this->app->bind(HwStationQaImportInterface::class, HwStationQaImportRepository::class);
        $this->app->bind(HwStationTelemetryImportInterface::class, HwStationTelemetryImportRepository::class);

        //Status Job
        $this->app->bind(JobStatusInterface::class, JobStatusRepository::class);

        // Bindings for DBF Import repositories
        $this->app->bind(PocoSiagasRepositoryInterface::class, PocoSiagasRepository::class);
        $this->app->bind(PocoRimasRepositoryInterface::class, PocoRimasRepository::class);

        // Binding for CNARH CSV Import
        $this->app->bind(CnarhRepositoryInterface::class, CnarhRepository::class);

         $this->app->bind(
            \App\Repositories\Interfaces\HwStationFlowForecastInterface::class,
            \App\Repositories\HwStationFlowForecastRepository::class
        );

        // Binding for Map Layers
        $this->app->bind(MapLayerRepositoryInterface::class, MapLayerRepository::class);

    }

    public function boot(): void
    {
        // Força HTTPS quando a aplicação está atrás de um proxy (ngrok, load balancer, etc)
        if (config('app.env') !== 'local' || request()->header('X-Forwarded-Proto') === 'https') {
            \URL::forceScheme('https');
        }
        
        // Ou de forma mais simples, sempre forçar HTTPS se vier do proxy:
        if (request()->header('X-Forwarded-Proto') === 'https') {
            \URL::forceScheme('https');
        }
    }
}
