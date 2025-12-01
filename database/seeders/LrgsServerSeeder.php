<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LrgsServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lrgs_servers')->insert([
            [
                'host' => 'cdabackup.wcda.noaa.gov',
                'main' => 0,
                'created_at' =>now(),
                'updated_at' => now()
            ],
            [
                'host' => 'lrgseddn2.cr.usgs.gov',
                'main' => 1,
                'created_at' =>now(),
                'updated_at' => now()
            ],
            [
                'host' => 'cdadata.wcda.noaa.gov',
                'main' => 0,
                'created_at' =>now(),
                'updated_at' => now()
            ],
            [
                'host' => 'lrgseddn1.cr.usgs.gov',
                'main' => 0,
                'created_at' =>now(),
                'updated_at' => now()
            ],
        ]);
    }
}


