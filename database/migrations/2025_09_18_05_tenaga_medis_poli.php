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
        Schema::create('tenaga_medis_poli', function (Blueprint $table) {
            $table->foreignId('tenaga_medis_id')->constrained('tenaga_medis', 'id_tenaga_medis')->cascadeOnDelete();
            $table->foreignId('poli_id')->constrained('poli', 'id_poli')->cascadeOnDelete();
            $table->primary(['tenaga_medis_id', 'poli_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenaga_medis_poli');
    }
};
