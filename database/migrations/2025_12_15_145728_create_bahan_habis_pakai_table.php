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
        Schema::create('bahan_habis_pakai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_farmasi_id')->nullable()->constrained(
                'brand_farmasi',
                'id',
                'bahan_habis_pakai_brand_farmasi_id'
            )->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('jenis_id')->nullable()->constrained(
                'jenis_obat',
                'id',
                'bahan_habis_pakai_jenis_id'
            )->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('satuan_id')->nullable()->constrained(
                'satuan_obat',
                'id',
                'bahan_habis_pakai_satuan_id'
            )->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('kode')->nullable();
            $table->string('nama_barang')->nullable();
            $table->unsignedInteger('stok_barang')->nullable();
            $table->decimal('dosis', 15, 2);
            $table->date('tanggal_kadaluarsa_bhp')->nullable();
            $table->string('no_batch')->nullable();
            $table->decimal('harga_beli_satuan_bhp', 15, 2)->nullable();
            $table->decimal('avg_hpp_bhp', 15, 2)->nullable()->default(0);
            $table->decimal('harga_jual_umum_bhp', 15, 2)->nullable();
            $table->decimal('harga_otc_bhp', 15, 2)->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_habis_pakai');
    }
};
