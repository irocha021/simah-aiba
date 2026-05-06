<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('map_layers')
            ->where('slug', 'municip_sei_2019')
            ->update(['border_buffer' => 1]);
    }

    public function down(): void
    {
        DB::table('map_layers')
            ->where('slug', 'municip_sei_2019')
            ->update(['border_buffer' => 1500]);
    }
};
