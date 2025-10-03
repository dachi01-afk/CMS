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
        Schema::create('detail_pembelian_obat', function (Blueprint $table) {
            $table->id('id_detail_pembelian');
            $table->foreignId('pembelian_obat_id')->constrained('pembelian_obat', 'id_pembelian_obat')->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('data_obat', 'id_obat')->cascadeOnDelete();
            $table->integer('jumlah');
            $table->decimal('harga_beli', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pembelian_obat');
    }
};
