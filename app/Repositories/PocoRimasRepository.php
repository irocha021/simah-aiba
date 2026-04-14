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
            $normalizedKey = strtolower(trim(str_replace(' ', '_', $key)));
            $normalized[$normalizedKey] = $value;
        }

        // Monta data_hora_medicao combinando data_da_me + hora_da_me
        $data = $normalized['data_da_me'] ?? null;
        $hora = $normalized['hora_da_me'] ?? null;

         if ($data && $hora) {
             $dt = \DateTime::createFromFormat('d/m/Y H:i:s', $data . ' ' . substr($hora, 0, 8));
             $normalized['data_hora_medicao'] = $dt ? $dt->format('Y-m-d H:i:s') : null;
         } else {
             $normalized['data_hora_medicao'] = null;
         }

        $normalized['created_at'] = date('Y-m-d H:i:s');
        $normalized['updated_at'] = date('Y-m-d H:i:s');

        return $normalized;
    }


    public function getAllWithCoordinates(): Collection
    {
        return PocoRimas::select('id_ponto', 'latitude_d', 'longitude')
            ->whereNotNull('latitude_d')
            ->whereNotNull('longitude')
            ->distinct()
            ->get();
    }

    public function getReadingsByIdPonto(string $idPonto, int $limit = 50)
    {
        return PocoRimas::where('id_ponto', $idPonto)
            ->orderBy('numero_de', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getReadingsByIdPontoAndDateRange(int $idPonto, ?string $dateFrom, ?string $dateTo): Collection
    {
        $query = PocoRimas::where('id_ponto', $idPonto)
            ->orderBy('data_hora_medicao', 'asc');

        if ($dateFrom) {
            $query->where('data_hora_medicao', '>=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo) {
            $query->where('data_hora_medicao', '<=', $dateTo . ' 23:59:59');
        }

        return $query->get();
    }

}
