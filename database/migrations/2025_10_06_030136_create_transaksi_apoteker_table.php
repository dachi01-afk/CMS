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
        Schema::create('transaksi_apoteker', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resep_id')->constrained('resep', 'id', 'transaksi_apoteker_resep_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('apoteker_id')->constrained('apoteker', 'id', 'transaksi_apoteker_apoteker_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->dateTime('tanggal_transaksi_apoteker');
            $table->decimal('total_harga', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_apoteker');
    }
};
