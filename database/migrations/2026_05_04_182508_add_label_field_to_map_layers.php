<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('map_layers', function (Blueprint $table) {
            $table->string('label_field')->nullable()->after('border_buffer');
        });

        DB::table('map_layers')
            ->where('slug', 'municip_sei_2019')
            ->update(['label_field' => 'municipio']);
    }

    public function down(): void
    {
        Schema::table('map_layers', function (Blueprint $table) {
            $table->dropColumn('label_field');
        });
    }
};
