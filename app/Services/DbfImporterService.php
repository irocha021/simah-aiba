<?php

namespace App\Services;

use App\Repositories\Interfaces\PocoSiagasRepositoryInterface;
use App\Repositories\Interfaces\PocoRimasRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DbfImporterService
{
    private DbfZipExtractorService $extractorService;
    private DbfReaderService $readerService;

    public function __construct(
        DbfZipExtractorService $extractorService,
        DbfReaderService $readerService
    ) {
        $this->extractorService = $extractorService;
        $this->readerService = $readerService;
    }

    /**
     * Importa dados de um arquivo ZIP contendo DBF para o banco de dados
     *
     * @param string $zipPath Caminho completo do arquivo ZIP
     * @param string $source Origem dos dados: 'siagas' ou 'rimas'
     * @return array Resultado da importação com estatísticas
     */
    public function import(string $zipPath, string $source): array
    {
        $totalImported = 0;
        $dbfFileName = null;
        $dbfPath = null;

        try {
            Log::info("Iniciando importação DBF", [
                'source' => $source,
                'zip_path' => $zipPath
            ]);

            // 1. Configura o extractor com o ZIP recebido
            $this->extractorService->setZipPath($zipPath);

            // 2. Extrai DBF SEM especificar nome (detecção automática)
            $extractResult = $this->extractorService->extractDbf();

            if (!$extractResult['success']) {
                throw new Exception($extractResult['message']);
            }

            $dbfPath = $extractResult['path'];
            $dbfFileName = $extractResult['filename'];

            Log::info("DBF extraído com sucesso", [
                'source' => $source,
                'detected_file' => $dbfFileName,
                'path' => $dbfPath
            ]);

            // 3. Configura o reader com o DBF extraído
            $this->readerService->setDbfPath($dbfPath);

            // Ativa preservação de precisão numérica
            $this->readerService->setPreserveNumericPrecision(true);

            // 4. Obtém o repository correto baseado no source
            $repository = $this->getRepository($source);

            // 5. Soft delete em todos os registros existentes antes de importar novos
            Log::info("Marcando registros existentes como deletados (soft delete)", ['source' => $source]);
            $repository->softDeleteAll();

            // 6. Lê e importa em batches
            $startTime = microtime(true);

            DB::beginTransaction();

            $result = $this->readerService->iterateRecords(
                function ($batch) use ($repository, &$totalImported) {
                    $count = $repository->createBatch($batch);
                    $totalImported += $count;

                    Log::info("Batch importado", [
                        'batch_size' => $count,
                        'total_imported' => $totalImported
                    ]);
                },
                500 // batch size
            );

            DB::commit();

            $duration = round(microtime(true) - $startTime, 2);

            Log::info("Importação concluída", [
                'source' => $source,
                'total_imported' => $totalImported,
                'duration' => $duration
            ]);

            // 6. Limpa arquivo temporário
            if ($dbfFileName) {
                $this->extractorService->cleanExtractedFile($dbfFileName);
            }

            return [
                'success' => true,
                'source' => $source,
                'detected_file' => $dbfFileName,
                'total_imported' => $totalImported,
                'duration_seconds' => $duration,
                'message' => 'Importação concluída com sucesso'
            ];

        } catch (Exception $e) {
            DB::rollBack();

            Log::error("Erro na importação", [
                'source' => $source,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Tenta limpar arquivo temporário mesmo em caso de erro
            if ($dbfFileName) {
                try {
                    $this->extractorService->cleanExtractedFile($dbfFileName);
                } catch (Exception $cleanupError) {
                    Log::warning("Erro ao limpar arquivo temporário", [
                        'file' => $dbfFileName,
                        'error' => $cleanupError->getMessage()
                    ]);
                }
            }

            return [
                'success' => false,
                'source' => $source,
                'detected_file' => $dbfFileName,
                'total_imported' => $totalImported,
                'message' => 'Erro na importação: ' . $e->getMessage()
            ];
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
            default => throw new Exception("Source inválido: {$source}. Use 'siagas' ou 'rimas'")
        };
    }
}
