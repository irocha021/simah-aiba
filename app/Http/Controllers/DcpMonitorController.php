<?php

namespace App\Http\Controllers;

use App\Services\DcpMonitor\DcpMonitorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DcpMonitorController extends Controller
{
    private DcpMonitorService $dcpService;

    public function __construct(DcpMonitorService $dcpService)
    {
        $this->dcpService = $dcpService;
    }

    /**
     * Busca dados completos do DCP com raw data
     */
    public function fetchDcpData(Request $request, ?string $dcpAddress = null): JsonResponse
    {
        try {
            $dcpAddress = $dcpAddress ?? $request->get('dcp_address', config('dcp.default_address'));
            $includeRawData = $request->boolean('include_raw_data', config('dcp.auto_fetch_raw_data'));

         
            $data = $this->dcpService->fetchCompleteData($dcpAddress, $includeRawData);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca apenas os links das mensagens
     */
    public function fetchMessageLinks(Request $request, ?string $dcpAddress = null): JsonResponse
    {
        try {
            $dcpAddress = $dcpAddress ?? $request->get('dcp_address', config('dcp.default_address'));
            $includeRawData = $request->boolean('include_raw_data', false);
            
            $links = $this->dcpService->fetchMessageLinks($dcpAddress, $includeRawData);
            
            return response()->json([
                'success' => true,
                'data' => $links
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca raw data de uma mensagem específica
     */
    public function fetchMessageRawData(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'message_filename' => 'required|string'
            ]);
            
            $rawData = $this->dcpService->fetchSingleMessageRawData($request->get('message_filename'));
            
            return response()->json([
                'success' => true,
                'data' => $rawData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}