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
        Schema::create('return_obat_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_obat_id')->unique()
                ->constrained('return_obat', 'id', 'return_obat_detail_return_obat_id')
                ->casCadeOnUpdate()->restrictOnDelete();
            $table->foreignId('obat_id')->constrained('obat', 'id', 'return_obat_detail_obat_id')
                ->casCadeOnUpdate()->restrictOnDelete();
            $table->foreignId('batch_obat_id')->constrained('batch_obat', 'id', 'return_obat_detail_batch_obat_id')
                ->casCadeOnUpdate()->restrictOnDelete();
            $table->unsignedBigInteger('qty')->default(1);
            $table->decimal('harga_beli', 18, 2)->default(0);
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(['return_obat_id', 'batch_obat_id'], 'uniq_return_obat_batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_obat_detail');
    }
};
