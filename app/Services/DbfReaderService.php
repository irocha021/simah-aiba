<?php

namespace App\Services;

use Exception;
use XBase\TableReader;
use Illuminate\Support\Facades\Log;

class DbfReaderService
{
    private string $dbfPath;
    private ?string $encoding;
    private bool $preserveNumericPrecision;
    private array $numericColumns = [];

    public function __construct(?string $encoding = null, bool $preserveNumericPrecision = false)
    {
        // Não define path padrão - deve ser configurado via setDbfPath()
        $this->dbfPath = '';
        $this->encoding = $encoding;
        $this->preserveNumericPrecision = $preserveNumericPrecision;
    }

    /**
     * Obtém a instância do TableReader
     *
     * @param array|null $columns Colunas específicas para carregar (otimização)
     * @return TableReader
     * @throws Exception
     */
    private function getTableReader(?array $columns = null): TableReader
    {
        if (!file_exists($this->dbfPath)) {
            throw new Exception("Arquivo DBF não encontrado: {$this->dbfPath}. Execute a extração primeiro.");
        }

        $options = [];

        if ($this->encoding) {
            $options['encoding'] = $this->encoding;
        }

        if ($columns) {
            $options['columns'] = $columns;
        }

        return new TableReader($this->dbfPath, $options);
    }

    /**
     * Carrega informações sobre colunas numéricas (para preservar precisão)
     *
     * @return void
     */
    private function loadNumericColumns(): void
    {
        if (!empty($this->numericColumns)) {
            return;
        }

        try {
            $table = $this->getTableReader();
            $columns = $table->getColumns();

            foreach ($columns as $column) {
                if (in_array($column->getType(), ['N', 'F'])) {
                    $this->numericColumns[$column->getName()] = [
                        'type' => $column->getType(),
                        'length' => $column->getLength(),
                        'decimals' => $column->getDecimalCount()
                    ];
                }
            }
        } catch (Exception $e) {
            Log::error("Erro ao carregar colunas numéricas", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Formata um registro preservando a precisão numérica se habilitado
     * e convertendo valores vazios para NULL
     *
     * @param array $recordData Dados do registro
     * @param int|null $recordIndex Índice do registro (para ler bytes brutos se necessário)
     * @return array
     */
    private function formatRecordData(array $recordData, ?int $recordIndex = null): array
    {
        $this->loadNumericColumns();

        // Carrega valores brutos do registro se possível
        $rawValues = null;
        if ($recordIndex !== null) {
            $rawValues = $this->getRawRecordValues($recordIndex);
        }

        $formatted = [];
        foreach ($recordData as $columnName => $value) {
            // Converte valores "vazios" para NULL real
            $value = $this->convertNullValues($columnName, $value, $rawValues);

            // Preserva precisão numérica se habilitado
            if ($this->preserveNumericPrecision && isset($this->numericColumns[$columnName]) && is_numeric($value)) {
                $decimals = $this->numericColumns[$columnName]['decimals'];
                // Formata como string com a precisão completa
                $formatted[$columnName] = number_format((float)$value, $decimals, '.', '');
            } else {
                $formatted[$columnName] = $value;
            }
        }

        return $formatted;
    }

    /**
     * Converte valores "vazios" para NULL real
     *
     * Alguns arquivos DBF representam NULL como:
     * - Strings vazias após trim
     * - Valores numéricos zero quando o campo não foi preenchido
     * - Asteriscos (*) repetidos em campos numéricos
     *
     * @param string $columnName Nome da coluna
     * @param mixed $value Valor a verificar
     * @param array|null $rawValues Valores brutos do DBF (se disponível)
     * @return mixed Valor original ou NULL
     */
    private function convertNullValues(string $columnName, $value, ?array $rawValues = null)
    {
        // Se já é null, retorna
        if ($value === null) {
            return null;
        }

        // Para strings: vazio após trim = NULL
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || $trimmed === '*') {
                return null;
            }
            // Se é uma string que contém apenas asteriscos, é NULL
            if (strlen($trimmed) > 0 && str_replace('*', '', $trimmed) === '') {
                return null;
            }
            return $value;
        }

        // Para números: verifica se o valor bruto contém asteriscos
        if (is_numeric($value) && $rawValues !== null && isset($rawValues[$columnName])) {
            $rawValue = trim($rawValues[$columnName]);
            // Se o valor bruto são asteriscos, é NULL
            if (strlen($rawValue) > 0 && str_replace('*', '', $rawValue) === '') {
                return null;
            }
            // Se o valor bruto está vazio ou só tem espaços, é NULL
            if ($rawValue === '') {
                return null;
            }
        }

        return $value;
    }

    /**
     * Lê os valores brutos de um registro do DBF
     * Útil para detectar valores NULL representados como asteriscos
     *
     * @param int $recordIndex Índice do registro (0-based)
     * @return array|null Array com valores brutos por nome de coluna, ou null se erro
     */
    private function getRawRecordValues(int $recordIndex): ?array
    {
        try {
            $table = $this->getTableReader();
            $columns = $table->getColumns();

            // Abre o arquivo DBF para leitura binária
            $fp = fopen($this->dbfPath, 'rb');
            if (!$fp) {
                return null;
            }

            // Calcula tamanho do registro
            $recordSize = 1; // deletion flag
            foreach ($columns as $column) {
                $recordSize += $column->getLength();
            }

            // Calcula posição do registro no arquivo
            // Header + (recordIndex * recordSize)
            $headerSize = $table->getHeaderLength();
            $recordPosition = $headerSize + ($recordIndex * $recordSize);

            // Move para a posição do registro
            fseek($fp, $recordPosition);

            // Lê o registro inteiro
            $recordBytes = fread($fp, $recordSize);
            fclose($fp);

            // Extrai valores por coluna
            $rawValues = [];
            $position = 1; // Skip deletion flag

            foreach ($columns as $column) {
                $columnName = $column->getName();
                $columnLength = $column->getLength();
                $rawValue = substr($recordBytes, $position, $columnLength);
                $rawValues[$columnName] = $rawValue;
                $position += $columnLength;
            }

            return $rawValues;

        } catch (Exception $e) {
            Log::warning("Erro ao ler valores brutos do registro", [
                'recordIndex' => $recordIndex,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Retorna informações sobre as colunas do DBF
     *
     * @return array
     */
    public function getColumns(): array
    {
        try {
            $table = $this->getTableReader();
            $columns = $table->getColumns();

            $columnInfo = [];
            foreach ($columns as $column) {
                $columnInfo[] = [
                    'name' => $column->getName(),
                    'type' => $column->getType(),
                    'length' => $column->getLength(),
                    'decimal_count' => $column->getDecimalCount(),
                ];
            }

            return [
                'success' => true,
                'columns' => $columnInfo,
                'total_columns' => count($columnInfo),
                'message' => 'Colunas recuperadas com sucesso'
            ];

        } catch (Exception $e) {
            Log::error("Erro ao obter colunas do DBF", ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'columns' => [],
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Retorna o número total de registros
     *
     * @return array
     */
    public function getTotalRecords(): array
    {
        try {
            $table = $this->getTableReader();
            $total = $table->getRecordCount();

            return [
                'success' => true,
                'total' => $total,
                'message' => "Total de {$total} registros"
            ];

        } catch (Exception $e) {
            Log::error("Erro ao obter total de registros", ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'total' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Retorna os primeiros N registros (útil para testes)
     *
     * @param int $limit
     * @return array
     */
    public function getFirstRecords(int $limit = 10): array
    {
        try {
            $table = $this->getTableReader();
            $records = [];
            $count = 0;

            while ($record = $table->nextRecord()) {
                if ($count >= $limit) {
                    break;
                }

                $records[] = $this->formatRecordData($record->getData(), $record->getRecordIndex());
                $count++;
            }

            return [
                'success' => true,
                'records' => $records,
                'count' => count($records),
                'message' => "Primeiros {$count} registros recuperados"
            ];

        } catch (Exception $e) {
            Log::error("Erro ao obter primeiros registros", ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'records' => [],
                'count' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Retorna registros em lotes (paginação)
     *
     * @param int $offset
     * @param int $limit
     * @param array|null $columns Colunas específicas para carregar
     * @return array
     */
    public function getRecordsChunk(int $offset = 0, int $limit = 100, ?array $columns = null): array
    {
        try {
            $table = $this->getTableReader($columns);
            $records = [];
            $count = 0;
            $currentIndex = 0;

            while ($record = $table->nextRecord()) {
                // Pula registros até chegar no offset
                if ($currentIndex < $offset) {
                    $currentIndex++;
                    continue;
                }

                // Para quando atingir o limite
                if ($count >= $limit) {
                    break;
                }

                $records[] = $this->formatRecordData($record->getData(), $record->getRecordIndex());
                $count++;
                $currentIndex++;
            }

            return [
                'success' => true,
                'records' => $records,
                'count' => count($records),
                'offset' => $offset,
                'limit' => $limit,
                'message' => "Recuperados {$count} registros"
            ];

        } catch (Exception $e) {
            Log::error("Erro ao obter chunk de registros", [
                'error' => $e->getMessage(),
                'offset' => $offset,
                'limit' => $limit
            ]);

            return [
                'success' => false,
                'records' => [],
                'count' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Itera sobre todos os registros executando um callback
     * Útil para processamento em lote ou importação
     *
     * @param callable $callback Função que recebe cada registro
     * @param int $batchSize Tamanho do lote para callback
     * @return array
     */
    public function iterateRecords(callable $callback, int $batchSize = 100): array
    {
        try {
            $table = $this->getTableReader();
            $totalProcessed = 0;
            $batch = [];

            while ($record = $table->nextRecord()) {
                $batch[] = $this->formatRecordData($record->getData(), $record->getRecordIndex());

                // Quando o batch atingir o tamanho especificado, executa o callback
                if (count($batch) >= $batchSize) {
                    $callback($batch, $totalProcessed);
                    $totalProcessed += count($batch);
                    $batch = [];
                }
            }

            // Processa registros restantes
            if (!empty($batch)) {
                $callback($batch, $totalProcessed);
                $totalProcessed += count($batch);
            }

            return [
                'success' => true,
                'total_processed' => $totalProcessed,
                'message' => "Processados {$totalProcessed} registros"
            ];

        } catch (Exception $e) {
            Log::error("Erro ao iterar sobre registros", ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'total_processed' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Busca registros por um valor específico em uma coluna
     * ATENÇÃO: Este método carrega todos os registros na memória
     *
     * @param string $columnName
     * @param mixed $value
     * @param int $limit Limite de resultados
     * @return array
     */
    public function searchRecords(string $columnName, $value, int $limit = 100): array
    {
        try {
            $table = $this->getTableReader();
            $records = [];
            $count = 0;

            while ($record = $table->nextRecord()) {
                if ($count >= $limit) {
                    break;
                }

                $recordData = $record->getData();

                // Verifica se a coluna existe e se o valor corresponde
                if (isset($recordData[$columnName]) && $recordData[$columnName] == $value) {
                    $records[] = $this->formatRecordData($recordData, $record->getRecordIndex());
                    $count++;
                }
            }

            return [
                'success' => true,
                'records' => $records,
                'count' => count($records),
                'search' => [
                    'column' => $columnName,
                    'value' => $value
                ],
                'message' => "Encontrados {$count} registros"
            ];

        } catch (Exception $e) {
            Log::error("Erro ao buscar registros", [
                'error' => $e->getMessage(),
                'column' => $columnName,
                'value' => $value
            ]);

            return [
                'success' => false,
                'records' => [],
                'count' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Retorna todos os registros
     * ATENÇÃO: Use com cuidado! Pode consumir muita memória com arquivos grandes
     *
     * @return array
     */
    public function getAllRecords(): array
    {
        try {
            $table = $this->getTableReader();
            $records = [];

            while ($record = $table->nextRecord()) {
                $records[] = $this->formatRecordData($record->getData(), $record->getRecordIndex());
            }

            return [
                'success' => true,
                'records' => $records,
                'count' => count($records),
                'message' => "Todos os registros carregados"
            ];

        } catch (Exception $e) {
            Log::error("Erro ao obter todos os registros", ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'records' => [],
                'count' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Define o encoding para leitura
     *
     * @param string $encoding
     * @return self
     */
    public function setEncoding(string $encoding): self
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * Define o caminho do arquivo DBF
     *
     * @param string $path
     * @return self
     */
    public function setDbfPath(string $path): self
    {
        $this->dbfPath = $path;
        return $this;
    }

    /**
     * Define se deve preservar a precisão numérica completa
     *
     * @param bool $preserve
     * @return self
     */
    public function setPreserveNumericPrecision(bool $preserve): self
    {
        $this->preserveNumericPrecision = $preserve;
        return $this;
    }
}
