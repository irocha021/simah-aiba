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
        return DcpReading::where('dcp_station_id', $stationId)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('year', [$startTime->year, $endTime->year])
                    ->whereBetween('julian_day', [$startTime->dayOfYear, $endTime->dayOfYear]);
            })
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

            // Recalcula flow_15min se water_level_15min foi recuperado
            if (isset($update['water_level_15min'])) {
                $reading = DcpReading::with('dcpStation')->find($id);
                if ($reading && $reading->dcpStation) {
                    $station = $reading->dcpStation;

                    if ($station->curva_chave && !is_null($station->a) && !is_null($station->b)) {
                        $h = (float) $update['water_level_15min'];
                        if ($station->curva_chave == 1) {
                            $update['flow_15min'] = \App\Helpers\Equations::calcularConversaoDaCargaHidraulicaEmVazaoDeRioPrimeira(
                                (float) $station->a,
                                (float) $station->b,
                                $h,
                                (float) $station->h0
                            );
                        } elseif ($station->curva_chave == 2) {
                            $update['flow_15min'] = \App\Helpers\Equations::calcularConversaoDaCargaHidraulicaEmVazaoDeRioSegunda(
                                (float) $station->a,
                                (float) $station->b,
                                (float) $station->c,
                                $h
                            );
                        }
                    }
                }
            }

            DcpReading::where('id', $id)->update($update);
        }

    }
}
