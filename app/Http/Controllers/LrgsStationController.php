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
        $ratingCurves = $station->ratingCurves()->orderBy('starts_at', 'desc')->get();
        $openCurve = $ratingCurves->firstWhere('ends_at', null);
        return view('lrgs-stations.edit', compact('station', 'ratingCurves', 'openCurve'));
    }

    public function update(Request $request, $id)
    {
        $station = DcpStation::findOrFail($id);

        $validated = $request->validate([
            'dcp_address'   => 'required|string|max:20|unique:dcp_stations,dcp_address,' . $id,
            'station_label' => 'required|string|max:255',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
        ]);

        $station->update($validated);

        return redirect()->route('lrgs-stations.edit', $id)
            ->with('success', 'Estação atualizada com sucesso!');
    }

    public function storeCurve(Request $request, $id)
    {
        $station = DcpStation::findOrFail($id);
        $curva = $request->input('curva_chave');
        $startsAt = $request->input('starts_at');

        $validated = $request->validate([
            'curva_chave' => 'required|in:1,2',
            'a'           => 'required|numeric',
            'b'           => 'required|numeric',
            'c'           => $curva == 2 ? 'required|numeric' : 'nullable|numeric',
            'h0'          => $curva == 1 ? 'required|numeric' : 'nullable|numeric',
            'starts_at'   => 'required|date|after_or_equal:today',
            'ends_at'     => ['nullable', 'date', 'after_or_equal:' . ($startsAt ?: 'today')],
        ], [
            'starts_at.after_or_equal' => 'A data de início não pode ser no passado.',
            'ends_at.after_or_equal'   => 'A data de fim deve ser igual ou posterior à data de início.',
        ]);

        if ($curva == 1) {
            $validated['c'] = null;
        } else {
            $validated['h0'] = null;
        }

        // Verificar sobreposição com curvas já existentes (exceto a em aberto que será fechada)
        $openCurve = $station->ratingCurves()->whereNull('ends_at')->first();

        $overlap = $station->ratingCurves()
            ->when($openCurve, fn($q) => $q->where('id', '!=', $openCurve->id))
            ->where('starts_at', '<=', $validated['ends_at'] ?? '9999-12-31')
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $validated['starts_at']))
            ->exists();

        if ($overlap) {
            return back()->withErrors([
                'starts_at' => 'Já existe uma curva chave com período sobreposto a este intervalo.',
            ])->withInput();
        }

        // Fechar curva em aberto automaticamente com ends_at = starts_at - 1 dia
        $closedPrevious = false;
        if ($openCurve) {
            $previousEndsAt = \Carbon\Carbon::parse($validated['starts_at'])->subDay()->toDateString();
            $openCurve->update(['ends_at' => $previousEndsAt]);
            $closedPrevious = true;
        }

        $station->ratingCurves()->create($validated);

        $message = 'Curva chave adicionada com sucesso!';
        if ($closedPrevious) {
            $message .= ' A curva anterior foi encerrada automaticamente em ' .
                \Carbon\Carbon::parse($validated['starts_at'])->subDay()->format('d/m/Y') . '.';
        }

        return redirect()->route('lrgs-stations.edit', $id)->with('success', $message);
    }

    public function updateCurve(Request $request, $id, $curveId)
    {
        $station = DcpStation::findOrFail($id);
        $curve = $station->ratingCurves()->findOrFail($curveId);

        $isCurrent = !$curve->starts_at->isFuture() && ($curve->ends_at === null || !$curve->ends_at->isPast());

        // Período passado: bloqueado
        if (!$curve->starts_at->isFuture() && !$isCurrent) {
            return back()->withErrors(['ends_at' => 'Não é permitido editar curvas de períodos passados.'])->withInput();
        }

        // Período corrente: só permite alterar ends_at
        if ($isCurrent) {
            $endsAt = $request->input('ends_at');

            if (!$endsAt) {
                return response()->json(['error' => 'Informe a nova data de fim.'], 422);
            }

            if ($endsAt < now()->toDateString()) {
                return response()->json(['error' => 'A data de fim deve ser hoje ou uma data futura.'], 422);
            }

            $overlap = $station->ratingCurves()
                ->where('id', '!=', $curveId)
                ->where('starts_at', '<=', $endsAt)
                ->exists();

            if ($overlap) {
                return response()->json(['error' => 'Existe(m) período(s) futuro(s) que se sobrepõem à nova data de fim. Remova-os antes de continuar.'], 422);
            }

            $curve->update(['ends_at' => $endsAt]);

            return response()->json(['success' => true]);
        }

        // Período futuro: permite editar tudo
        $curva    = $request->input('curva_chave');
        $startsAt = $request->input('starts_at');

        $validated = $request->validate([
            'curva_chave' => 'required|in:1,2',
            'a'           => 'required|numeric',
            'b'           => 'required|numeric',
            'c'           => $curva == 2 ? 'required|numeric' : 'nullable|numeric',
            'h0'          => $curva == 1 ? 'required|numeric' : 'nullable|numeric',
            'starts_at'   => 'required|date|after_or_equal:today',
            'ends_at'     => ['nullable', 'date', 'after_or_equal:' . ($startsAt ?: 'today')],
        ], [
            'starts_at.after_or_equal' => 'A data de início não pode ser no passado.',
            'ends_at.after_or_equal'   => 'A data de fim deve ser igual ou posterior à data de início.',
        ]);

        if ($curva == 1) {
            $validated['c']  = null;
        } else {
            $validated['h0'] = null;
        }

        // Preserva datas se curva já tem ends_at definido
        if ($curve->ends_at) {
            unset($validated['starts_at'], $validated['ends_at']);
        }

        // Verificar sobreposição com outras curvas
        $overlap = $station->ratingCurves()
            ->where('id', '!=', $curveId)
            ->where('starts_at', '<=', $validated['ends_at'] ?? '9999-12-31')
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $validated['starts_at'] ?? $curve->starts_at->toDateString()))
            ->exists();

        if ($overlap) {
            return back()->withErrors([
                'starts_at' => 'Já existe uma curva chave com período sobreposto a este intervalo.',
            ])->withInput();
        }

        $curve->update($validated);

        return redirect()->route('lrgs-stations.edit', $id)->with('success', 'Curva chave atualizada com sucesso!');
    }

    public function destroyCurve($id, $curveId)
    {
        $station = DcpStation::findOrFail($id);
        $curve = $station->ratingCurves()->findOrFail($curveId);

        if (!$curve->starts_at->isFuture()) {
            abort(403, 'Não é permitido excluir curvas com data de início no passado ou hoje.');
        }

        $curve->delete();

        return redirect()->route('lrgs-stations.edit', $id)
            ->with('success', 'Curva chave removida com sucesso!');
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
