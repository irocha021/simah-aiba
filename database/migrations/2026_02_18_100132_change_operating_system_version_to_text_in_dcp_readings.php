<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dcp_readings', function (Blueprint $table) {
            $table->text('operating_system_version')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dcp_readings', function (Blueprint $table) {
            $table->string('operating_system_version', 50)->nullable()->change();
        });
    }
};
