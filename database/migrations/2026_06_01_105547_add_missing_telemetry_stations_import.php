<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $codes = [46430000, 46528000, 46561000, 46561500, 46782000];

        foreach ($codes as $code) {
            DB::table('hw_station_telemetry_import')->insertOrIgnore([
                'station_code' => $code,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('hw_station_telemetry_import')
            ->whereIn('station_code', [46430000, 46528000, 46561000, 46561500, 46782000])
            ->delete();
    }
};
