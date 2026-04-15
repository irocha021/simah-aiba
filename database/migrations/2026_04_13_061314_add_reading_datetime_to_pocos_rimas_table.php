<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pocos_rimas', function (Blueprint $table) {
            $table->dateTime('data_hora_medicao')->nullable()->after('hora_da_me')->index();
        });
    }

    public function down(): void
    {
        Schema::table('pocos_rimas', function (Blueprint $table) {
            $table->dropColumn('data_hora_medicao');
        });
    }
};
