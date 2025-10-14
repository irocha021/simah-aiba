<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HwStationQaImport;

class HwStationQaImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HwStationQaImport::create([
            'station_code' => '46552000'
        ]);
    }
}