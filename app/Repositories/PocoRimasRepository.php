<?php

namespace App\Repositories;

use App\Models\PocoRimas;
use App\Repositories\Interfaces\PocoRimasRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PocoRimasRepository implements PocoRimasRepositoryInterface
{
    public function all(): Collection
    {
        return PocoRimas::all();
    }

    public function find(int $id): ?PocoRimas
    {
        return PocoRimas::find($id);
    }

    public function findByIdPonto(int $idPonto): ?PocoRimas
    {
        return PocoRimas::where('id_ponto', $idPonto)->first();
    }

    public function create(array $data): PocoRimas
    {
        return PocoRimas::create($data);
    }

    public function createBatch(array $records): int
    {
        try {
            // Normaliza os nomes das colunas do DBF para snake_case
            $normalizedRecords = array_map(function($record) {
                return $this->normalizeColumnNames($record);
            }, $records);

            // Insere em lote usando chunks para evitar erros de memória
            $chunks = array_chunk($normalizedRecords, 500);
            $totalInserted = 0;

            foreach ($chunks as $chunk) {
                DB::table('pocos_rimas')->insert($chunk);
                $totalInserted += count($chunk);
            }

            Log::info("Batch RIMAS inserido", [
                'total' => $totalInserted
            ]);

            return $totalInserted;

        } catch (\Exception $e) {
            Log::error("Erro ao inserir batch RIMAS", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        return PocoRimas::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        $poco = PocoRimas::find($id);
        return $poco ? $poco->delete() : false;
    }

    public function truncate(): bool
    {
        try {
            PocoRimas::query()->forceDelete();
            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao truncar pocos_rimas", ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function softDeleteAll(): bool
    {
        try {
            // Marca todos os registros (incluindo já deletados) com deleted_at
            PocoRimas::withTrashed()->update(['deleted_at' => now()]);
            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao soft delete all pocos_rimas", ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $query = PocoRimas::query();

        $total = $query->count();
        $lastPage = ceil($total / $perPage);

        $data = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
        ];
    }

    /**
     * Normaliza os nomes das colunas do DBF para o padrão do banco
     * DBF pode ter espaços e maiúsculas, precisamos converter para snake_case
     */
    private function normalizeColumnNames(array $record): array
    {
        $normalized = [];

        foreach ($record as $key => $value) {
            // Remove espaços e converte para snake_case
            $normalizedKey = strtolower(trim(str_replace(' ', '_', $key)));
            $normalized[$normalizedKey] = $value;
        }

        // Adiciona timestamps
        $normalized['created_at'] = now();
        $normalized['updated_at'] = now();

        return $normalized;
    }
}
