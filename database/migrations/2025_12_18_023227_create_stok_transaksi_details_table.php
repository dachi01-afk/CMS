<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stok_transaksi_detail', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stok_transaksi_id')
                ->constrained('stok_transaksi', 'id', 'stok_transaksi_detail_stok_transaksi_stok_transaksi_id')
                ->cascadeOnDelete()->cascadeOnUpdate();

            // ITEM
            $table->foreignId('obat_id')
                ->nullable()
                ->constrained('obat', 'id', 'stok_transaksi_detail_obat_id')
                ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignId('bahan_habis_pakai_id')
                ->nullable()
                ->constrained('bahan_habis_pakai', 'id', 'stok_transaksi_detail_bahan_habis_pakai_id')
                ->cascadeOnUpdate()->cascadeOnDelete();

            // BATCH & EXPIRED
            $table->string('batch')->nullable();
            $table->date('expired_date')->nullable();

            $table->integer('jumlah');

            $table->foreignId('satuan_id')
                ->nullable()
                ->constrained('satuan_obat', 'id', 'stok_transaksi_detail_satuan_id')
                ->cascadeOnUpdate()->cascadeOnDelete();

            $table->decimal('harga_beli', 15, 2)->nullable();

            $table->foreignId('depot_id')
                ->constrained('depot', 'id', 'stok_transaksi_detail_depot_id')
                ->cascadeOnUpdate()->cascadeOnDelete();

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_transaksi_detail');
    }
};
