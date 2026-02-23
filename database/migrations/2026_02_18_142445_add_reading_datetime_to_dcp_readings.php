<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dcp_readings', function (Blueprint $table) {
            $table->dateTime('reading_datetime')->nullable()->after('address');
            $table->index('reading_datetime');
        });
    }

    public function down(): void
    {
        Schema::table('dcp_readings', function (Blueprint $table) {
            $table->dropIndex(['reading_datetime']);
            $table->dropColumn('reading_datetime');
        });
    }
};
