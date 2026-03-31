<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dcp_readings', function (Blueprint $table) {
            $table->renameColumn('flow_15min', 'flow');
        });
    }

    public function down(): void
    {
        Schema::table('dcp_readings', function (Blueprint $table) {
            $table->renameColumn('flow', 'flow_15min');
        });
    }
};
