<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateHwStationDrainageTilesJob;
use App\Models\HwInventoryStationData;
use App\Models\HwStationDrainageLayer;
use App\Services\API_Hidroweb\HidrowebService;
use App\Services\HidroInventoryStationService;
use App\Services\HidroStationQaImportService;
use App\Services\HidroStationTelemetryImportService;
use Illuminate\Http\Request;
use ZipArchive;

class HwInventoryStationController extends Controller
{
    public function __construct(
        protected HidrowebService $apiHidroweb,
        protected HidroInventoryStationService $stationService,
        protected HidroStationTelemetryImportService $telemetryImportService,
        protected HidroStationQaImportService $qaImportService,
    ) {}

    public function index(Request $request)
    {
        $type = $request->input('type', 'all');

        $stations = match($type) {
            'telemetry'     => $this->stationService->getStationsByType('telemetry', false),
            'water_quality' => $this->stationService->getStationsByType('water_quality', false),
            default         => $this->stationService->getAll(),
        };

        $telemetryCodes     = $this->telemetryImportService->getAll()->pluck('station_code')->toArray();
        $qaCodes            = $this->qaImportService->getAll()->pluck('station_code')->toArray();
        $allRegisteredCodes = array_unique(array_merge($telemetryCodes, $qaCodes));

        $stations = $stations->filter(fn($s) => in_array($s->station_code, $allRegisteredCodes))
            ->sortBy('station_name')
            ->values();


        return view('hw-inventory-stations.index', compact('stations', 'type'));
    }



    public function create()
    {
        return view('hw-inventory-stations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'station_code'               => 'required|integer',
            'telemetry_station_type'     => 'nullable|boolean',
            'water_quality_station_type' => 'nullable|boolean',
            'alfa_pond'                  => 'nullable|numeric',
            'q_noventa'                  => 'nullable|numeric',
            'vsup'                       => 'nullable|numeric',
        ]);

        $stationCode       = (int) $request->input('station_code');
        $isTelemetry       = (bool) $request->input('telemetry_station_type', false);
        $isWaterQuality    = (bool) $request->input('water_quality_station_type', false);

        // Busca dados na API
        try {
            $apiResponse = $this->apiHidroweb->fetchHidroInventarioEstacaoByCode($stationCode);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors([
                'station_code' => 'Não foi possível comunicar com a API HidroWeb da ANA. Tente novamente em alguns instantes.',
            ]);
        }

        if (empty($apiResponse['items'])) {
            return back()->withInput()->withErrors([
                'station_code' => 'Estação não encontrada na API HidroWeb. Verifique o código informado.',
            ]);
        }

        $station = $apiResponse['items'][0];

        $apiData = [
            'station_code'               => $station['codigoestacao'],
            'station_name'               => $station['Estacao_Nome'],
            'station_uf'                 => $station['UF_Estacao'],
            'station_uf_name'            => $station['UF_Nome_Estacao'],
            'basin_code'                 => $station['codigobacia'],
            'basin_name'                 => $station['Bacia_Nome'],
            'altitude'                   => $station['Altitude'],
            'latitude'                   => $station['Latitude'],
            'longitude'                  => $station['Longitude'],
            'is_operational'             => $station['Operando'],
            'telemetry_station_type'     => $isTelemetry,
            'water_quality_station_type' => $isWaterQuality,
            'responsible_code'           => $station['Responsavel_Codigo'],
            'responsible_acronym'        => $station['Responsavel_Sigla'],
            'responsible_unit_uf'        => $station['Responsavel_Unidade_UF'],
            'operator_code'              => $station['Operadora_Codigo'],
            'operator_abbreviation'      => $station['Operadora_Sigla'],
            'operator_sub_unit_state'    => $station['Operadora_Sub_Unidade_UF'],
        ];

        $extraData = [
            'alfa_pond'  => $request->input('alfa_pond'),
            'q_noventa'  => $request->input('q_noventa'),
            'vsup'       => $request->input('vsup'),
        ];

        $this->stationService->storeFromApi($stationCode, $apiData, $extraData);

        if ($isTelemetry) {
            $this->telemetryImportService->storeByStationCode($stationCode);
        }

        if ($isWaterQuality) {
            $this->qaImportService->storeByStationCode($stationCode);
        }

        return redirect()->route('hw-inventory-stations.index')
            ->with('success', 'Estação cadastrada com sucesso!');
    }

    public function edit(int $code)
    {
        $station = $this->stationService->getByStationCode($code);

        if (!$station) {
            return redirect()->route('hw-inventory-stations.index')
                ->withErrors(['Estação não encontrada.']);
        }

        $stationData = HwInventoryStationData::where('station_code', $code)->first();
        $drainage = HwStationDrainageLayer::where('station_code', $code)->first();

        return view('hw-inventory-stations.edit', compact('station', 'stationData', 'drainage'));
    }


    public function update(Request $request, int $code)
    {
        $request->validate([
            'telemetry_station_type'     => 'nullable|boolean',
            'water_quality_station_type' => 'nullable|boolean',
            'alfa_pond'                  => 'nullable|numeric',
            'q_noventa'                  => 'nullable|numeric',
            'vsup'                       => 'nullable|numeric',
        ]);

        $isTelemetry    = (bool) $request->input('telemetry_station_type', false);
        $isWaterQuality = (bool) $request->input('water_quality_station_type', false);

        $this->stationService->changeStatus($code, [
            'telemetry_station_type'     => $isTelemetry,
            'water_quality_station_type' => $isWaterQuality,
        ]);

        HwInventoryStationData::updateOrCreate(
            ['station_code' => $code],
            [
                'alfa_pond' => $request->input('alfa_pond'),
                'q_noventa' => $request->input('q_noventa'),
                'vsup'      => $request->input('vsup'),
            ]
        );

        // Atualiza tabelas de import conforme checkboxes
        if ($isTelemetry) {
            $this->telemetryImportService->storeByStationCode($code);
        } else {
            $this->telemetryImportService->deleteByStationCode($code);
        }

        if ($isWaterQuality) {
            $this->qaImportService->storeByStationCode($code);
        } else {
            $this->qaImportService->deleteByStationCode($code);
        }

        return redirect()->route('hw-inventory-stations.index')
            ->with('success', 'Estação atualizada com sucesso!');
    }

    public function destroy(int $code)
    {
        $this->telemetryImportService->deleteByStationCode($code);
        $this->qaImportService->deleteByStationCode($code);
        HwInventoryStationData::where('station_code', $code)->delete();
        $this->stationService->destroy($code);

        return redirect()->route('hw-inventory-stations.index')
            ->with('success', 'Estação removida com sucesso!');
    }

    public function uploadDrainage(Request $request, int $code)
    {
        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:51200', // 50MB
        ]);

        $station = $this->stationService->getByStationCode($code);
        if (!$station) {
            return redirect()->route('hw-inventory-stations.index')
                ->withErrors(['Estação não encontrada.']);
        }

        $targetDir = storage_path('app/hw_station_drainages');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = "$targetDir/$code.zip";
        $request->file('zip_file')->move($targetDir, "$code.zip");

        // Valida que o ZIP contém os 4 arquivos do shapefile
        $zip = new ZipArchive;
        if ($zip->open($targetPath) !== true) {
            @unlink($targetPath);
            return back()->withErrors(['zip_file' => 'Não foi possível abrir o ZIP.']);
        }

        $exts = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $exts[] = strtolower(pathinfo($zip->getNameIndex($i), PATHINFO_EXTENSION));
        }
        $zip->close();

        foreach (['shp', 'shx', 'dbf', 'prj'] as $required) {
            if (!in_array($required, $exts)) {
                @unlink($targetPath);
                return back()->withErrors(['zip_file' => "ZIP precisa conter arquivo .$required"]);
            }
        }

        HwStationDrainageLayer::updateOrCreate(
            ['station_code' => $code],
            [
                'zip_path'       => "storage/app/hw_station_drainages/$code.zip",
                'status'         => 'pending',
                'status_message' => null,
            ]
        );

        GenerateHwStationDrainageTilesJob::dispatch($code);

        return back()->with('success', 'Shapefile enviado. A área de drenagem está sendo gerada.');
    }

    public function regenerateDrainage(int $code)
    {
        $row = HwStationDrainageLayer::where('station_code', $code)->first();

        if (!$row || !$row->zip_path) {
            return back()->withErrors(['Drenagem não encontrada — faça upload do shapefile primeiro.']);
        }

        $row->update(['status' => 'pending', 'status_message' => null]);

        GenerateHwStationDrainageTilesJob::dispatch($code);

        return back()->with('success', 'Geração da área de drenagem reiniciada.');
    }

    public function drainageStatus(int $code)
    {
        $d = HwStationDrainageLayer::where('station_code', $code)->first();

        return response()->json([
            'status'             => $d?->status,
            'status_message'     => $d?->status_message,
            'tiles_generated_at' => $d?->tiles_generated_at?->format('d/m/Y H:i'),
        ]);
    }
}
