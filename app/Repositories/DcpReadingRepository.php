<?php

namespace App\Repositories;

use App\Models\DcpReading;
use App\Repositories\Interfaces\DcpReadingRepositoryInterface;
use Illuminate\Support\Collection;

class DcpReadingRepository implements DcpReadingRepositoryInterface
{
    public function all(): Collection
    {
        return DcpReading::all();
    }

    public function find(int $id): ?DcpReading
    {
        return DcpReading::find($id);
    }

    public function findByAddress(string $address, int $limit = 50)
    {
        return DcpReading::where('address', $address)
            ->orderBy('reading_datetime', 'desc')
            ->limit($limit)
            ->get();
    }

    public function findByAddressAndDateRange(string $address, string $dateFrom, string $dateTo)
    {
        return DcpReading::where('address', $address)
            ->where('reading_datetime', '>=', $dateFrom)
            ->where('reading_datetime', '<=', $dateTo)
            ->orderBy('reading_datetime', 'desc')
            ->get();
    }

    public function cursorByAddressAndDateRange(string $address, ?string $dateFrom, ?string $dateTo): \Generator
    {
        $query = DcpReading::where('address', $address)
            ->when($dateFrom, fn($q) => $q->where('reading_datetime', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->where('reading_datetime', '<=', $dateTo))
            ->orderBy('reading_datetime', 'desc');

        if (!$dateFrom && !$dateTo) {
            $query->limit(72);
        }

        foreach ($query->cursor() as $record) {
            yield $record;
        }
    }


    public function create(array $data): DcpReading
    {
        return DcpReading::create($data);
    }

    public function bulkInsert(array $readings): bool
    {
        return DcpReading::insert($readings);
    }

    public function update(int $id, array $data): bool
    {
        return DcpReading::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return DcpReading::destroy($id);
    }

    public function findByStationId(int $stationId): Collection
    {
        return DcpReading::where('dcp_station_id', $stationId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByDateRange(int $stationId, string $startDate, string $endDate): Collection
    {
        return DcpReading::where('dcp_station_id', $stationId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function softDeleteByStationAndPeriod(int $stationId, $startTime, $endTime): int
    {
        // Usa reading_datetime (intervalo real) — antes era um produto cartesiano de
        // year/julian_day em whereBetween, que apagava registros fora da janela quando
        // o período cruzava a virada de ano ou abrangia múltiplos dias.
        return DcpReading::where('dcp_station_id', $stationId)
            ->whereBetween('reading_datetime', [$startTime, $endTime])
            ->delete(); // SoftDeletes trait makes this a soft delete
    }

    public function findPreviousReading(string $address, string $readingDatetime): ?DcpReading
    {
        $expectedPrevious = \Carbon\Carbon::parse($readingDatetime)->subHour();

        return DcpReading::where('address', $address)
            ->where('reading_datetime', $expectedPrevious)
            ->first();
    }

    public function updateNullReadings(int $id, array $data): void
    {
        $update = [];

        $waterFields = [
            'water_level_60min',
            'water_level_45min',
            'water_level_30min',
            'water_level_15min',
        ];

        $rainFields = [
            'rain_60min',
            'rain_45min',
            'rain_30min',
            'rain_15min',
        ];

        foreach ($waterFields as $field) {
            if (isset($data[$field]) && $data[$field] !== null) {
                $update[$field] = $data[$field];
            }
        }

        foreach ($rainFields as $field) {
            if (isset($data[$field]) && $data[$field] !== null) {
                $update[$field] = $data[$field];
            }
        }

        if (!empty($update)) {
            $update['recovered_at'] = now();

            // Busca o primeiro water level disponível na sequência
            $reading = DcpReading::with('dcpStation')->find($id);
            if ($reading && $reading->dcpStation) {
                $readingDatetime = $reading->reading_datetime;

                $waterLevel = $reading->water_level_15min ?? $reading->water_level_30min ?? $reading->water_level_45min ?? $reading->water_level_60min;

                // Sobrescreve com os valores recém recuperados se existirem
                $waterLevel = $update['water_level_15min'] ?? $update['water_level_30min'] ?? $update['water_level_45min'] ?? $update['water_level_60min'] ?? $waterLevel;

                if ($waterLevel !== null) {
                    $curve = $reading->dcpStation->ratingCurves()
                        ->where('starts_at', '<=', $readingDatetime->toDateString())
                        ->where(function ($q) use ($readingDatetime) {
                            $q->whereNull('ends_at')
                              ->orWhere('ends_at', '>=', $readingDatetime->toDateString());
                        })
                        ->first();

                    if ($curve) {
                        $h = (float) $waterLevel;
                        if ($curve->curva_chave == 1) {
                            $update['flow'] = \App\Helpers\Equations::calcularConversaoDaCargaHidraulicaEmVazaoDeRioPrimeira(
                                (float) $curve->a, (float) $curve->b, $h, (float) $curve->h0
                            );
                        } elseif ($curve->curva_chave == 2) {
                            $update['flow'] = \App\Helpers\Equations::calcularConversaoDaCargaHidraulicaEmVazaoDeRioSegunda(
                                (float) $curve->a, (float) $curve->b, (float) $curve->c, $h
                            );
                        }
                    }
                }
            }

            DcpReading::where('id', $id)->update($update);
        }

    }

    /**
     * Insere a leitura se ainda não existir para (dcp_station_id, reading_datetime).
     * Considera registros soft-deleted: se encontrar um, restaura.
     *
     * @return array{model: DcpReading, created: bool, restored: bool}
     */
    public function firstOrCreateForKey(array $data): array
    {
        $existing = DcpReading::withTrashed()
            ->where('dcp_station_id', $data['dcp_station_id'])
            ->where('reading_datetime', $data['reading_datetime'])
            ->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
                return ['model' => $existing, 'created' => false, 'restored' => true];
            }
            return ['model' => $existing, 'created' => false, 'restored' => false];
        }

        return [
            'model'    => DcpReading::create($data),
            'created'  => true,
            'restored' => false,
        ];
    }

}
