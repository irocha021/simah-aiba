<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dcp_failure_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique();
            $table->text('description');
            $table->timestamps();
            
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dcp_failure_codes');
    }
};