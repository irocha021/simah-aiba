<?php

namespace App\Http\Controllers;

use App\Services\DbfReaderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DbfReaderController extends Controller
{
    private DbfReaderService $readerService;

    public function __construct(DbfReaderService $readerService)
    {
        $this->readerService = $readerService;
    }

    /**
     * Retorna informações sobre as colunas do DBF
     *
     * @return JsonResponse
     */
    public function columns(): JsonResponse
    {
        $result = $this->readerService->getColumns();
        $statusCode = $result['success'] ? 200 : 500;

        return response()->json($result, $statusCode);
    }

    /**
     * Retorna o total de registros
     *
     * @return JsonResponse
     */
    public function total(): JsonResponse
    {
        $result = $this->readerService->getTotalRecords();
        $statusCode = $result['success'] ? 200 : 500;

        return response()->json($result, $statusCode);
    }

    /**
     * Retorna os primeiros N registros
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function first(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $limit = min(max((int)$limit, 1), 1000); // Entre 1 e 1000

        // Opção para preservar precisão numérica
        $preservePrecision = $request->boolean('preserve_precision', false);
        $this->readerService->setPreserveNumericPrecision($preservePrecision);

        $result = $this->readerService->getFirstRecords($limit);
        $statusCode = $result['success'] ? 200 : 500;

        return response()->json($result, $statusCode);
    }

    /**
     * Retorna registros em lotes (paginação)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function records(Request $request): JsonResponse
    {
        $offset = max((int)$request->get('offset', 0), 0);
        $limit = min(max((int)$request->get('limit', 100), 1), 1000);

        // Colunas específicas (opcional)
        $columns = $request->get('columns');
        if ($columns && is_string($columns)) {
            $columns = explode(',', $columns);
            $columns = array_map('trim', $columns);
        } else {
            $columns = null;
        }

        // Opção para preservar precisão numérica
        $preservePrecision = $request->boolean('preserve_precision', false);
        $this->readerService->setPreserveNumericPrecision($preservePrecision);

        $result = $this->readerService->getRecordsChunk($offset, $limit, $columns);
        $statusCode = $result['success'] ? 200 : 500;

        return response()->json($result, $statusCode);
    }

    /**
     * Busca registros por valor em uma coluna
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'column' => 'required|string',
            'value' => 'required'
        ]);

        $column = $request->get('column');
        $value = $request->get('value');
        $limit = min(max((int)$request->get('limit', 100), 1), 1000);

        $result = $this->readerService->searchRecords($column, $value, $limit);
        $statusCode = $result['success'] ? 200 : 500;

        return response()->json($result, $statusCode);
    }

    /**
     * Retorna informações gerais do DBF
     *
     * @return JsonResponse
     */
    public function info(): JsonResponse
    {
        $columns = $this->readerService->getColumns();
        $total = $this->readerService->getTotalRecords();

        $result = [
            'success' => $columns['success'] && $total['success'],
            'info' => [
                'total_records' => $total['total'] ?? 0,
                'total_columns' => $columns['total_columns'] ?? 0,
                'columns' => $columns['columns'] ?? []
            ],
            'message' => 'Informações do DBF recuperadas'
        ];

        return response()->json($result);
    }
}
