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
        Schema::create('return_bahan_habis_pakai_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_bahan_habis_pakai_id')
                ->constrained('return_bahan_habis_pakai', 'id', 'return_bahan_habis_pakai_detail_return_bahan_habis_pakai_id')
                ->casCadeOnUpdate()->restrictOnDelete();
            $table->foreignId('bahan_habis_pakai_id')->constrained('bahan_habis_pakai', 'id', 'return_bahan_habis_pakai_detail_bahan_habis_pakai_id')
                ->casCadeOnUpdate()->restrictOnDelete();
            $table->foreignId('batch_bahan_habis_pakai_id')->constrained('batch_bahan_habis_pakai', 'id', 'return_bahan_habis_pakai_detail_batch_bahan_habis_pakai_id')
                ->casCadeOnUpdate()->restrictOnDelete();
            $table->unsignedBigInteger('qty')->default(1);
            $table->decimal('harga_beli', 18, 2)->default(0);
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(['return_bahan_habis_pakai_id', 'batch_bahan_habis_pakai_id'], 'uniq_return_bahan_habis_pakai_batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_bahan_habis_pakai_detail');
    }
};
