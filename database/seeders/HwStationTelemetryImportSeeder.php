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
            46409990,
            46411000,
            46413000,
            46415000,
            46417000,
            46450500,
            46451000,
            46453000,
            46453500,
            46455000,
            46520000,
            46520010,
            46527000,
            46543000,
            46550000,
            46570500,
            46590000,
            46610000,
            46675000,
            46770000,
            46790000,
            46830000,
            46870000,
            46902000,
            46902010,
            46998000,
            1246024,
            1245070,
            1245069,
            1245068,
        ];

        foreach ($stationCodes as $code) {
            HwStationTelemetryImport::create([
                'station_code' => $code
            ]);
        }
    }
}