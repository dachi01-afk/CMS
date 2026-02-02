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
        Schema::create('batch_bahan_habis_pakai_depot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_bahan_habis_pakai_id')
                ->constrained('batch_bahan_habis_pakai', 'id', 'batch_bahan_habis_pakai_depot_batch_bahan_habis_pakai_id')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('depot_id')
                ->constrained('depot', 'id', 'batch_bahan_habis_pakai_depot_depot_id')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('stok_bahan_habis_pakai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_bahan_habis_pakai_depot');
    }
};
