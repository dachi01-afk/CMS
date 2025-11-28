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
        Schema::create('perawat_dokter_poli', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perawat_id')->nullable()->constrained('perawat', 'id', 'perawat_dokter_poli_perawat_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('dokter_poli_id')->nullable()->constrained('dokter_poli', 'id', 'perawat_dokter_poli_dokter_poli_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perawat_dokter_poli');
    }
};
