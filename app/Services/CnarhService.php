<?php

namespace App\Services;

use App\Repositories\Interfaces\CnarhRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CnarhService
{
    private CnarhRepositoryInterface $repository;
    private CnarhImportService $importService;

    public function __construct(
        CnarhRepositoryInterface $repository,
        CnarhImportService $importService
    ) {
        $this->repository = $repository;
        $this->importService = $importService;
    }

    public function importFromCsv(string $filePath): array
    {
        $startTime = microtime(true);

        try {
            DB::beginTransaction();

            Log::info("Iniciando importação CNARH", ['file' => $filePath]);

            $data = $this->importService->import($filePath);

            if (empty($data)) {
                throw new \Exception("Nenhum registro encontrado no arquivo CSV");
            }

            Log::info("Deletando registros existentes");
            $this->repository->deleteAll();

            Log::info("Inserindo novos registros", ['total' => count($data)]);
            $this->repository->insertBatch($data);

            DB::commit();

            $duration = round(microtime(true) - $startTime, 2);

            Log::info("Importação CNARH concluída", [
                'total_imported' => count($data),
                'duration_seconds' => $duration
            ]);

            return [
                'success' => true,
                'total_imported' => count($data),
                'duration_seconds' => $duration,
                'message' => "Importação concluída com sucesso"
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Erro na importação CNARH", [
                'error' => $e->getMessage(),
                'file' => $filePath
            ]);

            return [
                'success' => false,
                'total_imported' => 0,
                'duration_seconds' => round(microtime(true) - $startTime, 2),
                'message' => "Erro na importação: " . $e->getMessage()
            ];
        }
    }
}
