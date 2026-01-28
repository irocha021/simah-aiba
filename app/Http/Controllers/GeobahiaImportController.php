<?php

namespace App\Http\Controllers;

use App\Services\MapLayerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Artisan;

class GeobahiaImportController extends Controller
{

    public function __construct(
        private MapLayerService $mapLayerService
    ) {}

    public function import($slug, $minZoom = null, $maxZoom = null, $opacity = null): JsonResponse
    {
        $layer = $this->mapLayerService->getLayerBySlug($slug);

        if (!$layer) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Camada não encontrada.'
            ], 404);
        }

        // Atualiza o banco se algum parâmetro foi passado
        if ($minZoom !== null || $maxZoom !== null || $opacity !== null) {
            $this->mapLayerService->updateLayerSettings(
                $layer['id'],
                $minZoom !== null ? (int) $minZoom : null,
                $maxZoom !== null ? (int) $maxZoom : null,
                $opacity !== null ? (float) $opacity : null
            );
        }

        if ($layer['type'] === 'tile') {
            return $this->importTile($layer, $minZoom, $maxZoom);
        } elseif ($layer['type'] === 'geojson') {
            return $this->importGeojson($layer);
        } else {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Tipo de camada não suportado: ' . $layer['type']
            ], 400);
        }
    }

    private function importTile(array $layer, $minZoom = null, $maxZoom = null): JsonResponse
    {
        $pathZip = base_path($layer['path_zip']);

        $parametros = [
            'shapefile'  => $pathZip,
            '--layer'    => $layer['slug'],
            '--zoom-min' => $minZoom ?? $layer['min_zoom'],
            '--zoom-max' => $maxZoom ?? $layer['max_zoom']
        ];

        try {
            $exitCode = Artisan::call('tiles:generate', $parametros);
            $output = Artisan::output();

            if ($exitCode !== 0) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Erro ao gerar tiles.',
                    'log_output' => $output
                ], 500);
            }

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Camada [' . $layer['slug'] . '] (tile) importada com sucesso!',
                'log_output' => $output
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ], 500);
        }
    }

    private function importGeojson(array $layer): JsonResponse
    {
        $pathZip = $layer['path_zip'];

        $parametros = [
            'zipPath' => $pathZip,
            '--layer'   => $layer['slug']
        ];

        try {
            $exitCode = Artisan::call('geojson:generate', $parametros);
            $output = Artisan::output();

            if ($exitCode !== 0) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Erro ao converter shapefile para GeoJSON.',
                    'log_output' => $output
                ], 500);
            }

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Camada [' . $layer['slug'] . '] (geojson) importada com sucesso!',
                'log_output' => $output
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ], 500);
        }
    }

    public function importAll(): JsonResponse
    {

        ignore_user_abort();
        ini_set('max_execution_time', 0);
        ini_set("memory_limit",-1);

        $layers = $this->mapLayerService->getAllLayers();
        $resultados = [];
        $erros = 0;
        $sucessos = 0;

        foreach ($layers as $layer) {
            $layerArray = $layer->toArray();
            
            try {
                if ($layerArray['type'] === 'tile') {
                    $response = $this->importTile($layerArray);
                } elseif ($layerArray['type'] === 'geojson') {
                    $response = $this->importGeojson($layerArray);
                } else {
                    $resultados[] = [
                        'slug' => $layerArray['slug'],
                        'status' => 'ignorado',
                        'mensagem' => 'Tipo não suportado: ' . $layerArray['type']
                    ];
                    continue;
                }

                $resultado = json_decode($response->getContent(), true);
                $resultados[] = [
                    'slug' => $layerArray['slug'],
                    'status' => $resultado['status'],
                    'mensagem' => $resultado['mensagem']
                ];

                if ($resultado['status'] === 'sucesso') {
                    $sucessos++;
                } else {
                    $erros++;
                }

            } catch (Exception $e) {
                $erros++;
                $resultados[] = [
                    'slug' => $layerArray['slug'],
                    'status' => 'erro',
                    'mensagem' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'status' => $erros === 0 ? 'sucesso' : 'parcial',
            'mensagem' => "Importação concluída: {$sucessos} sucesso(s), {$erros} erro(s).",
            'total' => count($layers),
            'sucessos' => $sucessos,
            'erros' => $erros,
            'detalhes' => $resultados
        ]);
    }
}
