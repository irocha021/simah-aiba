<?php

namespace App\Http\Controllers;

use App\Models\DcpStation;
use Illuminate\Http\Request;

class LrgsStationController extends Controller
{
    public function index()
    {
        $stations = DcpStation::orderBy('station_label')->paginate(20);
        return view('lrgs-stations.index', compact('stations'));
    }

    public function create()
    {
        return view('lrgs-stations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dcp_address'   => 'required|string|max:20|unique:dcp_stations,dcp_address',
            'station_label' => 'required|string|max:255',
        ], [
            'dcp_address.required'   => 'O endereço DCP é obrigatório.',
            'dcp_address.unique'     => 'Este endereço DCP já está cadastrado.',
            'station_label.required' => 'O nome da estação é obrigatório.',
        ]);

        DcpStation::create($validated);

        return redirect()->route('lrgs-stations.index')
            ->with('success', 'Estação cadastrada com sucesso!');
    }

    public function edit($id)
    {
        $station = DcpStation::findOrFail($id);
        return view('lrgs-stations.edit', compact('station'));
    }

    public function update(Request $request, $id)
    {
        $station = DcpStation::findOrFail($id);

        $curva = $request->input('curva_chave');

        $rules = [
            'dcp_address'   => 'required|string|max:20|unique:dcp_stations,dcp_address,' . $id,
            'station_label' => 'required|string|max:255',
            'curva_chave'   => 'required|in:1,2',
            'a'             => 'required|numeric',
            'b'             => 'required|numeric',
            'h0'            => 'required|numeric',
            'c'             => $curva == 2 ? 'required|numeric' : 'nullable|numeric',
        ];

        $validated = $request->validate($rules);

        if ($curva == 1) {
            $validated['c'] = null;
        }

        $station->update($validated);

        return redirect()->route('lrgs-stations.index')
            ->with('success', 'Estação atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $station = DcpStation::findOrFail($id);
        $station->dcp_address = $station->dcp_address . '_removed_' . $station->id;
        $station->save();
        $station->delete();

        return redirect()->route('lrgs-stations.index')
            ->with('success', 'Estação removida com sucesso!');
    }

}
