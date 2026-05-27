<?php

namespace App\Repositories;

use App\Models\PocoSimahReading;
use App\Repositories\Interfaces\PocoSimahReadingRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PocoSimahReadingRepository implements PocoSimahReadingRepositoryInterface
{
    public function upsertBatch(int $stationId, array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        try {
            $now = now()->toDateTimeString();

            $rows = array_map(function ($record) use ($stationId, $now) {
                return array_merge($record, [
                    'poco_simah_station_id' => $stationId,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);
            }, $records);

            $chunks = array_chunk($rows, 500);

            foreach ($chunks as $chunk) {
                PocoSimahReading::upsert(
                    $chunk,
                    ['poco_simah_station_id', 'datetime_utc'],
                    ['number', 'datetime_local', 'pd_bar', 'p1_bar', 'water_level_meters', 'p2_bar', 'tob1_celsius', 'tob2_celsius', 'updated_at']
                );
            }

            return count($rows);

        } catch (\Exception $e) {
            Log::error('Erro ao inserir leituras SIMAH', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getByStation(int $stationId, int $limit = 100): array
    {
        return PocoSimahReading::where('poco_simah_station_id', $stationId)
            ->orderBy('datetime_utc', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function deleteByStation(int $stationId): bool
    {
        try {
            PocoSimahReading::where('poco_simah_station_id', $stationId)->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao deletar leituras SIMAH', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getByStationAndDateRange(int $stationId, string $dateFrom, string $dateTo): Collection
    {
        return PocoSimahReading::where('poco_simah_station_id', $stationId)
            ->where('datetime_local', '>=', $dateFrom . ' 00:00:00')
            ->where('datetime_local', '<=', $dateTo . ' 23:59:59')
            ->orderBy('datetime_local', 'asc')
            ->get();
    }

    public function cursorByStationAndDateRange(int $stationId, ?string $dateFrom, ?string $dateTo): \Generator
    {
        $query = PocoSimahReading::where('poco_simah_station_id', $stationId)
            ->when($dateFrom, fn($q) => $q->where('datetime_local', '>=', $dateFrom . ' 00:00:00'))
            ->when($dateTo,   fn($q) => $q->where('datetime_local', '<=', $dateTo   . ' 23:59:59'))
            ->orderBy('datetime_local', 'desc');

        if (!$dateFrom && !$dateTo) {
            $query->limit(100);
        }

        foreach ($query->cursor() as $record) {
            yield $record;
        }
    }


}
