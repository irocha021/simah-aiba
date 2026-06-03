<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('hw_station_drainage_layers')->update(['max_zoom' => 12]);
    }

    public function down(): void
    {
        DB::table('hw_station_drainage_layers')->update(['max_zoom' => 11]);
    }
};
