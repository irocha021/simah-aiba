<?php

namespace App\Repositories;

use App\Models\Cnarh;
use App\Repositories\Interfaces\CnarhRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CnarhRepository implements CnarhRepositoryInterface
{
    public function deleteAll(): void
    {
        try {
            Cnarh::withTrashed()->update(['deleted_at' => now()]);
            Log::info("Todos os registros CNARH foram soft deleted");
        } catch (\Exception $e) {
            Log::error("Erro ao soft delete all cnarh", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function insertBatch(array $data): void
    {
        try {
            $chunks = array_chunk($data, 50);
            $totalInserted = 0;

            foreach ($chunks as $chunk) {
                DB::table('cnarh')->insert($chunk);
                $totalInserted += count($chunk);
                Log::info("Chunk CNARH inserido", ['count' => count($chunk)]);
            }

            Log::info("Batch CNARH completo", ['total' => $totalInserted]);
        } catch (\Exception $e) {
            Log::error("Erro ao inserir batch CNARH", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getAllWithCoordinates()
    {
        return Cnarh::whereNotNull('int_nu_latitude')
            ->whereNotNull('int_nu_longitude')
            ->select(['int_cd_cnarh40', 'int_nu_cnarh', 'emp_nm_empreendimento', 'int_nu_latitude', 'int_nu_longitude'])
            ->cursor();
    }

    public function getByCnarh(string $intCdCnarh40)
    {
        return Cnarh::where('int_cd_cnarh40', $intCdCnarh40)->first();
    }
}
