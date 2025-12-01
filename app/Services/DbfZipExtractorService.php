<?php

namespace App\Services;

use Exception;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DbfZipExtractorService
{
    private string $zipPath;
    private string $extractPath;

    public function __construct()
    {
        $this->zipPath = public_path('Pocos_Siagas.zip');
        $this->extractPath = storage_path('app/dbf_temp');
    }

    /**
     * Define o caminho do arquivo ZIP dinamicamente
     *
     * @param string $path
     * @return self
     */
    public function setZipPath(string $path): self
    {
        $this->zipPath = $path;
        return $this;
    }

    /**
     * Encontra o primeiro arquivo .dbf dentro do ZIP
     *
     * @return string|null Nome do arquivo .dbf ou null se não encontrar
     */
    public function findFirstDbfFile(): ?string
    {
        try {
            if (!file_exists($this->zipPath)) {
                Log::warning("ZIP não encontrado ao buscar DBF", ['path' => $this->zipPath]);
                return null;
            }

            $zip = new ZipArchive();
            $openResult = $zip->open($this->zipPath);

            if ($openResult !== true) {
                Log::warning("Erro ao abrir ZIP para buscar DBF", ['code' => $openResult]);
                return null;
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);

                // Verifica se é um arquivo .dbf (case-insensitive)
                if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'dbf') {
                    $zip->close();
                    Log::info("Arquivo DBF detectado automaticamente", ['file' => $filename]);
                    return $filename;
                }
            }

            $zip->close();
            Log::warning("Nenhum arquivo .dbf encontrado no ZIP");
            return null;

        } catch (Exception $e) {
            Log::error("Erro ao buscar DBF no ZIP", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Extrai o arquivo DBF do ZIP
     *
     * @param string|null $dbfFileName Nome do arquivo DBF dentro do ZIP (null = auto-detecta)
     * @return array Contém ['success' => bool, 'path' => string|null, 'filename' => string|null, 'message' => string]
     * @throws Exception
     */
    public function extractDbf(?string $dbfFileName = null): array
    {
        try {
            // Se não especificar o nome, detecta automaticamente
            if ($dbfFileName === null) {
                $dbfFileName = $this->findFirstDbfFile();

                if ($dbfFileName === null) {
                    throw new Exception("Nenhum arquivo .dbf encontrado dentro do ZIP");
                }

                Log::info("Usando arquivo DBF detectado automaticamente", ['file' => $dbfFileName]);
            }

            // Verifica se o arquivo ZIP existe
            if (!file_exists($this->zipPath)) {
                throw new Exception("Arquivo ZIP não encontrado: {$this->zipPath}");
            }

            // Cria o diretório temporário se não existir
            if (!is_dir($this->extractPath)) {
                mkdir($this->extractPath, 0755, true);
            }

            // Abre o arquivo ZIP
            $zip = new ZipArchive();
            $openResult = $zip->open($this->zipPath);

            if ($openResult !== true) {
                throw new Exception("Erro ao abrir o arquivo ZIP. Código: {$openResult}");
            }

            // Verifica se o arquivo DBF existe dentro do ZIP
            if ($zip->locateName($dbfFileName) === false) {
                $zip->close();
                throw new Exception("Arquivo '{$dbfFileName}' não encontrado dentro do ZIP");
            }

            // Extrai apenas o arquivo DBF
            $extractResult = $zip->extractTo($this->extractPath, $dbfFileName);
            $zip->close();

            if (!$extractResult) {
                throw new Exception("Erro ao extrair o arquivo DBF");
            }

            $extractedFilePath = $this->extractPath . DIRECTORY_SEPARATOR . $dbfFileName;

            // Verifica se o arquivo foi extraído com sucesso
            if (!file_exists($extractedFilePath)) {
                throw new Exception("Arquivo DBF não foi extraído corretamente");
            }

            Log::info("Arquivo DBF extraído com sucesso", [
                'file' => $dbfFileName,
                'path' => $extractedFilePath,
                'size' => filesize($extractedFilePath)
            ]);

            return [
                'success' => true,
                'path' => $extractedFilePath,
                'filename' => $dbfFileName,
                'size' => filesize($extractedFilePath),
                'message' => 'Arquivo DBF extraído com sucesso'
            ];

        } catch (Exception $e) {
            Log::error("Erro ao extrair DBF do ZIP", [
                'error' => $e->getMessage(),
                'file' => $this->zipPath
            ]);

            return [
                'success' => false,
                'path' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Lista todos os arquivos dentro do ZIP
     *
     * @return array
     */
    public function listZipContents(): array
    {
        try {
            if (!file_exists($this->zipPath)) {
                throw new Exception("Arquivo ZIP não encontrado");
            }

            $zip = new ZipArchive();
            if ($zip->open($this->zipPath) !== true) {
                throw new Exception("Erro ao abrir o arquivo ZIP");
            }

            $files = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $files[] = [
                    'name' => $stat['name'],
                    'size' => $stat['size'],
                    'compressed_size' => $stat['comp_size']
                ];
            }

            $zip->close();

            return [
                'success' => true,
                'files' => $files,
                'message' => 'Arquivos listados com sucesso'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'files' => [],
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Remove o arquivo DBF extraído
     *
     * @param string|null $dbfFileName
     * @return bool
     */
    public function cleanExtractedFile(?string $dbfFileName = 'Pocos_Siagas.dbf'): bool
    {
        $filePath = $this->extractPath . DIRECTORY_SEPARATOR . $dbfFileName;

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }
}
