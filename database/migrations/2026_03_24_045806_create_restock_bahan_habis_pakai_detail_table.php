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
        Schema::create('restock_bahan_habis_pakai_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restock_bahan_habis_pakai_id')
                ->constrained('restock_bahan_habis_pakai', 'id', 'restock_bahan_habis_pakai_detail_restock_bahan_habis_pakai_id')
                ->casCadeOnUpdate()->restrictOnDelete();
            $table->foreignId('bahan_habis_pakai_id')
                ->constrained('bahan_habis_pakai', 'id', 'bahan_habis_pakai_detail_bahan_habis_pakai_id')
                ->casCadeOnUpdate()->restrictOnDelete();
            $table->foreignId('batch_bahan_habis_pakai_id')
                ->constrained('batch_bahan_habis_pakai', 'id', 'batch_bahan_habis_pakai_detail_batch_bahan_habis_pakai_id')
                ->casCadeOnUpdate()->restrictOnDelete();
            $table->integer('qty')->default(1);
            $table->decimal('harga_beli', 18, 2);
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->enum('diskon_type', ['nominal', 'persen'])->nullable();
            $table->decimal('diskon_value', 18, 2)->default(0);
            $table->decimal('diskon_amount', 18, 2)->default(0);
            $table->decimal('total_setelah_diskon', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restock_bahan_habis_pakai_detail');
    }
};
