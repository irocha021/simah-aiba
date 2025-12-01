<?php

namespace App\Http\Controllers;

use App\Services\CnarhService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CnarhUploadController extends Controller
{
    private CnarhService $cnarhService;

    public function __construct(CnarhService $cnarhService)
    {
        $this->cnarhService = $cnarhService;
    }

    public function index()
    {
        return view('cnarh.upload');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:51200'
        ]);

        try {
            $uploadedFile = $request->file('file');

            Log::info("Upload CSV CNARH iniciado", [
                'original_name' => $uploadedFile->getClientOriginalName(),
                'size' => $uploadedFile->getSize()
            ]);

            $uploadDir = storage_path('app/uploads/csv');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $csvFileName = 'cnarh_' . time() . '.csv';
            $fullCsvPath = $uploadDir . '/' . $csvFileName;

            $uploadedFile->move($uploadDir, $csvFileName);

            Log::info("Arquivo CSV salvo", [
                'full_path' => $fullCsvPath,
                'exists' => file_exists($fullCsvPath)
            ]);

            $result = $this->cnarhService->importFromCsv($fullCsvPath);

            if (file_exists($fullCsvPath)) {
                unlink($fullCsvPath);
            }

            return response()->json($result, $result['success'] ? 200 : 500);
 
        } catch (\Exception $e) {
            Log::error("Erro no upload CSV CNARH", [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }
}
