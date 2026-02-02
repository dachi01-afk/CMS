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
        Schema::create('batch_bahan_habis_pakai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_habis_pakai_id')
                ->constrained('bahan_habis_pakai', 'id', 'batch_bahan_habis_pakai_bahan_habis_pakai_id')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nama_batch')->unique();
            $table->date('tanggal_kadaluarsa_bahan_habis_pakai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_bahan_habis_pakai');
    }
};
