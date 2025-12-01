<?php

namespace App\Http\Controllers;

use App\Services\DbfImporterService;
use App\Repositories\Interfaces\PocoSiagasRepositoryInterface;
use App\Repositories\Interfaces\PocoRimasRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class DbfImportController extends Controller
{
    private DbfImporterService $importerService;

    public function __construct(DbfImporterService $importerService)
    {
        $this->importerService = $importerService;
    }

    /**
     * Exibe a página de upload
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('dbf-import.upload');
    }

    /**
     * Upload e importação de arquivo ZIP contendo DBF
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        // Aumenta o tempo máximo de execução para 5 minutos
        set_time_limit(300);

        $request->validate([
            'file' => 'required|file|mimes:zip|max:51200', // 50MB
            'source' => 'required|in:siagas,rimas'
        ]);

        try {
            $source = $request->input('source');
            $uploadedFile = $request->file('file');

            Log::info("Upload iniciado", [
                'source' => $source,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'size' => $uploadedFile->getSize()
            ]);

            // Cria o diretório se não existir
            $uploadDir = storage_path('app/uploads/dbf');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Salva ZIP temporariamente usando move()
            $zipFileName = $source . '_' . time() . '.zip';
            $fullZipPath = $uploadDir . '/' . $zipFileName;

            // Move o arquivo para o destino
            $uploadedFile->move($uploadDir, $zipFileName);

            Log::info("Arquivo ZIP salvo", [
                'full_path' => $fullZipPath,
                'exists' => file_exists($fullZipPath),
                'is_readable' => is_readable($fullZipPath),
                'filesize' => file_exists($fullZipPath) ? filesize($fullZipPath) : 0
            ]);

            // Importa (detecção automática do .dbf dentro)
            $result = $this->importerService->import($fullZipPath, $source);

            // Limpa o ZIP
            if (file_exists($fullZipPath)) {
                unlink($fullZipPath);
            }

            $statusCode = $result['success'] ? 200 : 500;

            return response()->json([
                'success' => $result['success'],
                'source' => $result['source'],
                'detected_dbf_file' => $result['detected_file'] ?? null,
                'total_imported' => $result['total_imported'],
                'duration_seconds' => $result['duration_seconds'] ?? null,
                'message' => $result['message']
            ], $statusCode);

        } catch (Exception $e) {
            Log::error("Erro no upload", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista registros com paginação
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $request->validate([
            'source' => 'required|in:siagas,rimas',
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:1000'
        ]);

        try {
            $source = $request->input('source');
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 15);

            $repository = $this->getRepository($source);
            $result = $repository->paginate($limit, $page);

            return response()->json([
                'success' => true,
                'source' => $source,
                'data' => $result['data'],
                'pagination' => [
                    'current_page' => $result['current_page'],
                    'per_page' => $result['per_page'],
                    'total' => $result['total'],
                    'last_page' => $result['last_page']
                ]
            ]);

        } catch (Exception $e) {
            Log::error("Erro ao listar registros", [
                'source' => $request->input('source'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar registros: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibe detalhes de um registro específico
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'source' => 'required|in:siagas,rimas'
        ]);

        try {
            $source = $request->input('source');
            $repository = $this->getRepository($source);
            $record = $repository->find($id);

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'source' => $source,
                'data' => $record
            ]);

        } catch (Exception $e) {
            Log::error("Erro ao buscar registro", [
                'source' => $request->input('source'),
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar registro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete de um registro
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'source' => 'required|in:siagas,rimas'
        ]);

        try {
            $source = $request->input('source');
            $repository = $this->getRepository($source);
            $deleted = $repository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'source' => $source,
                'message' => 'Registro removido com sucesso'
            ]);

        } catch (Exception $e) {
            Log::error("Erro ao deletar registro", [
                'source' => $request->input('source'),
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar registro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtém o repository correto baseado no source
     *
     * @param string $source
     * @return PocoSiagasRepositoryInterface|PocoRimasRepositoryInterface
     * @throws Exception
     */
    private function getRepository(string $source)
    {
        return match ($source) {
            'siagas' => app(PocoSiagasRepositoryInterface::class),
            'rimas' => app(PocoRimasRepositoryInterface::class),
            default => throw new Exception("Source inválido: {$source}")
        };
    }
}
