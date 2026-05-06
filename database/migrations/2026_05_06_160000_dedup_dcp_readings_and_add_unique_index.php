<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Limpa registros duplicados em dcp_readings e cria índice único.
 *
 * Critério de duplicata: mesma chave (dcp_station_id, reading_datetime).
 * Estratégia: mantém o id MENOR de cada grupo, hard-delete dos demais.
 *   - Hard delete (não soft) é necessário porque registros soft-deleted
 *     ocupam o índice único em MySQL.
 *   - dcp_reading_flows tem cascadeOnDelete, então flows associados saem juntos.
 *
 * Após a limpeza, adiciona unique index (dcp_station_id, reading_datetime),
 * que serve como defesa em profundidade no nível do banco.
 * O firstOrCreateForKey do repository continua tratando soft-deleted via restore().
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Hard-delete de duplicatas, preservando o id MIN por chave.
        $deleted = DB::delete('
            DELETE r1 FROM dcp_readings r1
            INNER JOIN dcp_readings r2 ON
                r1.dcp_station_id   = r2.dcp_station_id
                AND r1.reading_datetime = r2.reading_datetime
                AND r1.id > r2.id
            WHERE r1.reading_datetime IS NOT NULL
        ');

        Log::info('Migration dedup_dcp_readings: duplicatas removidas', [
            'rows_deleted' => $deleted,
        ]);

        // 2) Verificação defensiva: se ainda houver duplicata, aborta antes do unique.
        $remaining = DB::selectOne('
            SELECT COUNT(*) AS n FROM (
                SELECT 1
                FROM dcp_readings
                WHERE reading_datetime IS NOT NULL
                GROUP BY dcp_station_id, reading_datetime
                HAVING COUNT(*) > 1
            ) AS d
        ')->n;

        if ((int) $remaining > 0) {
            throw new \RuntimeException(
                "Ainda existem {$remaining} grupos de duplicatas em dcp_readings após a limpeza. "
                . "Investigue antes de aplicar o unique index."
            );
        }

        // 3) Cria o índice único.
        Schema::table('dcp_readings', function (Blueprint $table) {
            $table->unique(
                ['dcp_station_id', 'reading_datetime'],
                'dcp_readings_station_datetime_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('dcp_readings', function (Blueprint $table) {
            $table->dropUnique('dcp_readings_station_datetime_unique');
        });
        // Não há como reverter os registros hard-deletados.
    }
};
