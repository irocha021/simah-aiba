<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateHwStationDrainageTilesJob;
use App\Models\HwInventoryStation;
use App\Models\HwStationDrainageLayer;
use Illuminate\Http\JsonResponse;


class HwStationDrainageController extends Controller
{
    /**
     * Processa em batch todos os zips disponíveis em storage/app/hw_station_drainages/.
     * Cria/atualiza rows e dispara um job por estação que tenha cadastro em hw_inventory_stations.
     * Retorna imediatamente — o processamento dos tiles é assíncrono via worker.
     */
    public function importAll(): JsonResponse
    {
        $dir = storage_path('app/hw_station_drainages');

        if (!is_dir($dir)) {
            return response()->json([
                'status'   => 'erro',
                'mensagem' => "Diretório não encontrado: {$dir}",
            ], 404);
        }

        $zipFiles = glob("{$dir}/*.zip");

        $dispatched = [];
        $skipped    = [];

        foreach ($zipFiles as $zipPath) {
            $code = (int) pathinfo($zipPath, PATHINFO_FILENAME);

            // Estação tem que existir em hw_inventory_stations (FK)
            $stationExists = HwInventoryStation::where('station_code', $code)->exists();
            if (!$stationExists) {
                $skipped[] = $code;
                continue;
            }

            HwStationDrainageLayer::updateOrCreate(
                ['station_code' => $code],
                [
                    'zip_path'       => "storage/app/hw_station_drainages/{$code}.zip",
                    'status'         => 'pending',
                    'status_message' => null,
                ]
            );

            GenerateHwStationDrainageTilesJob::dispatch($code);
            $dispatched[] = $code;
        }

        return response()->json([
            'status'          => 'sucesso',
            'mensagem'        => count($dispatched) . ' estações enviadas para processamento em segundo plano.',
            'dispatched'      => $dispatched,
            'skipped'         => $skipped,
            'skipped_reason'  => 'Estações sem cadastro em hw_inventory_stations',
        ]);
    }
}
