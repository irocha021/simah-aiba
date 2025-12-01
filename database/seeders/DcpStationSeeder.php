<?php
// database/seeders/DcpStationSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DcpStationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('dcp_stations')->insert([
            [
                'dcp_address' => 'B04041E0',
                'station_name' => 'ASSG20161206',
                'station_label' => 'Estação Principal - Rio São Francisco',
                'channel' => 59,
                'transmission_interval' => '01:00:00',
                'first_transmission_time' => '00:06:20',
                'transmission_window' => 10,
                'baud_rate' => 300,
                'preamble' => 'S',
                'is_active' => true,
                'latitude' => -12.1436,
                'longitude' => -45.0108,
                'last_successful_transmission_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);
    }
}