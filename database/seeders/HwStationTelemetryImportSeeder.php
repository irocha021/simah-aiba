<?php

namespace Database\Seeders;

use App\Models\HwStationTelemetryImport;
use Illuminate\Database\Seeder;

class HwStationTelemetryImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stationCodes = [
          '18407500'
        ];

        foreach ($stationCodes as $code) {
            HwStationTelemetryImport::create([
                'station_code' => $code
            ]);
        }
    }
}