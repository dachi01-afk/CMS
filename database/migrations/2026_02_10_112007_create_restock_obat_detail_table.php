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
        Schema::create('restock_obat_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restock_obat_id')->constrained(
                'restock_obat',
                'id',
                'restock_obat_detail_restock_obat_id',
            )->cascadeOnUpdate()->casCadeOnDelete();
            $table->foreignId('obat_id')->constrained(
                'obat',
                'id',
                'restock_obat_detail_obat_id',
            )->cascadeOnUpdate()->casCadeOnDelete();
            $table->foreignId('batch_obat_id')->constrained(
                'batch_obat',
                'id',
                'restock_obat_detail_batch_obat_id',
            )->cascadeOnUpdate()->casCadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restock_obat_detail');
    }
};
