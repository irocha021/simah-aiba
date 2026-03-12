<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('map_layers')->update(['opacity' => 1]);
    }

    public function down(): void
    {
        DB::table('map_layers')->update(['opacity' => 0.80]);
    }
};
