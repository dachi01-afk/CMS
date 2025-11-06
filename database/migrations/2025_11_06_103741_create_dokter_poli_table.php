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
        Schema::create('dokter_poli', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokter_id')
                ->nullable()
                ->constrained('dokter', 'id', 'dokter_poli_dokter_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('poli_id')
                ->nullable()
                ->constrained('poli', 'id', 'dokter_poli_poli_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();

            // 1 dokter tidak boleh terdaftar dua kali di poli yang sama
            $table->unique(['dokter_id', 'poli_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokter_poli');
    }
};
