<?php

namespace App\Http\Controllers;

use App\Services\PocoSimahStationService;
use App\Services\PocoSimahImportService;
use Illuminate\Http\Request;

class PocoSimahStationController extends Controller
{
    protected $stationService;
    protected $importService;

    public function __construct(
        PocoSimahStationService $stationService,
        PocoSimahImportService $importService
    ) {
        $this->stationService = $stationService;
        $this->importService  = $importService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['name', 'station_code']);
        $result  = $this->stationService->paginate(20, array_merge($filters, ['page' => $request->get('page', 1)]));

        return view('poco-simah.stations.index', $result);
    }

    public function create(Request $request)
    {
        $prefilledCode = $request->get('station_code');
        return view('poco-simah.stations.create', compact('prefilledCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'station_code' => 'required|string|max:100|unique:poco_simah_stations,station_code',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'depth'        => 'nullable|numeric|min:0',
            'ativa'        => 'boolean',
        ], [
            'name.required'         => 'O nome da estação é obrigatório.',
            'station_code.required' => 'O código da estação é obrigatório.',
            'station_code.unique'   => 'Este código de estação já está cadastrado.',
            'latitude.between'      => 'Latitude deve estar entre -90 e 90.',
            'longitude.between'     => 'Longitude deve estar entre -180 e 180.',
            'depth.numeric'         => 'A profundidade deve ser um número.',
            'depth.min'             => 'A profundidade não pode ser negativa.',
        ]);

        $validated['ativa'] = $request->has('ativa');

        $station = $this->stationService->create($validated);

        return redirect()->route('poco-simah.stations.import', $station->id)
            ->with('success', 'Estação cadastrada com sucesso! Agora importe as leituras.');
    }

    public function edit(int $id)
    {
        $station = $this->stationService->find($id);

        if (!$station) {
            abort(404);
        }

        return view('poco-simah.stations.edit', compact('station'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'station_code' => 'required|string|max:100|unique:poco_simah_stations,station_code,' . $id,
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'depth'        => 'nullable|numeric|min:0',
            'ativa'        => 'boolean',
        ], [
            'name.required'         => 'O nome da estação é obrigatório.',
            'station_code.required' => 'O código da estação é obrigatório.',
            'station_code.unique'   => 'Este código de estação já está cadastrado.',
            'latitude.between'      => 'Latitude deve estar entre -90 e 90.',
            'longitude.between'     => 'Longitude deve estar entre -180 e 180.',
            'depth.numeric'         => 'A profundidade deve ser um número.',
            'depth.min'             => 'A profundidade não pode ser negativa.',
        ]);

        $validated['ativa'] = $request->has('ativa');

        $this->stationService->update($id, $validated);

        return redirect()->route('poco-simah.stations.edit', $id)
            ->with('success', 'Estação atualizada com sucesso!');
    }

    public function destroy(int $id)
    {
        $this->stationService->delete($id);

        return redirect()->route('poco-simah.stations.index')
            ->with('success', 'Estação removida com sucesso!');
    }

    public function checkCode(Request $request)
    {
        $code    = $request->get('station_code');
        $station = $this->stationService->findByCode($code);

        return response()->json([
            'exists'     => $station !== null,
            'station_id' => $station?->id,
        ]);
    }

    public function showImport(int $id)
    {
        $station = $this->stationService->find($id);

        if (!$station) {
            abort(404);
        }

        return view('poco-simah.import.import', compact('station'));
    }

    public function selectStation()
    {
        $result   = $this->stationService->paginate(200, []);
        $stations = $result['data']->map(function ($s) {
            return [
                'id'    => $s->id,
                'name'  => $s->name,
                'code'  => $s->station_code,
                'ativa' => $s->ativa,
            ];
        })->values();

        return view('poco-simah.import.select-station', compact('stations'));
    }



    public function import(Request $request, int $id)
    {
        $station = $this->stationService->find($id);

        if (!$station) {
            abort(404);
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:20480',
        ], [
            'csv_file.required' => 'Selecione um arquivo CSV.',
            'csv_file.mimes'    => 'O arquivo deve ser do tipo CSV.',
            'csv_file.max'      => 'O arquivo não pode ultrapassar 20MB.',
        ]);

        $result = $this->importService->import($id, $request->file('csv_file'));

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json($result);
        }

        return redirect()->route('poco-simah.stations.import', $id)
            ->with('import_result', $result);
    }
}
