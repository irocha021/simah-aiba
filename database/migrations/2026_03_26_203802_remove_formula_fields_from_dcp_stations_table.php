<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dcp_stations', function (Blueprint $table) {
            $table->dropColumn(['curva_chave', 'a', 'b', 'c', 'h0']);
        });
    }

    public function down(): void
    {
        Schema::table('dcp_stations', function (Blueprint $table) {
            $table->tinyInteger('curva_chave')->nullable();
            $table->decimal('a', 20, 15)->nullable();
            $table->decimal('b', 20, 15)->nullable();
            $table->decimal('c', 20, 15)->nullable();
            $table->decimal('h0', 20, 15)->nullable();
        });
    }
};
