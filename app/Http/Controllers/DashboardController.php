<?php

namespace App\Http\Controllers;

use App\Services\MapLayerService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private MapLayerService $mapLayerService
    ) {}

    public function index()
    {
        $mapLayers = $this->mapLayerService->getLayersForMap();
        
        return view('dashboard', compact('mapLayers'));
    }

    public function index2()
    {
        $mapLayers = $this->mapLayerService->getLayersForMap();
        
        return view('dashboard2', compact('mapLayers'));
    }
}
