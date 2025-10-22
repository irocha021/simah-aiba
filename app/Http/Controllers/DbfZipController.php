<?php

namespace App\Http\Controllers;

use App\Services\DbfZipExtractorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DbfZipController extends Controller
{
    private DbfZipExtractorService $extractorService;

    public function __construct(DbfZipExtractorService $extractorService)
    {
        $this->extractorService = $extractorService;
    }

    /**
     * Extrai o arquivo DBF do ZIP
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function extract(Request $request): JsonResponse
    {
        $dbfFileName = $request->get('filename', 'Pocos_Siagas.dbf'); 

        $result = $this->extractorService->extractDbf($dbfFileName);

        $statusCode = $result['success'] ? 200 : 500;

        return response()->json($result, $statusCode);
    }

    /**
     * Lista todos os arquivos dentro do ZIP
     *
     * @return JsonResponse
     */
    public function listFiles(): JsonResponse
    {
        $result = $this->extractorService->listZipContents();

        $statusCode = $result['success'] ? 200 : 500;

        return response()->json($result, $statusCode);
    }

    /**
     * Remove o arquivo DBF extraído
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clean(Request $request): JsonResponse
    {
        $dbfFileName = $request->get('filename', 'Pocos_Rimas.dbf');

        $deleted = $this->extractorService->cleanExtractedFile($dbfFileName);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted
                ? 'Arquivo removido com sucesso'
                : 'Arquivo não encontrado ou erro ao remover'
        ], $deleted ? 200 : 404);
    }
}
