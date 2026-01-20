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
        Schema::create('layanan_poli', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layanan_id')->nullable()->constrained(
                'layanan',
                'id',
                'layanan_poli_layanan_id'
            )->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('poli_id')->nullable()->constrained(
                'poli',
                'id',
                'layanan_poli_poli_id'
            )->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layanan_poli');
    }
};
