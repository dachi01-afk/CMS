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
        Schema::create('mutasi_stok_obat_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mutasi_stok_obat_id')
                ->constrained('mutasi_stok_obat', 'id', 'mutasi_stok_obat_mutasi_stok_obat_id')
                ->casCadeOnUpdate()->casCadeOnDelete();
            $table->foreignId('obat_id')
                ->constrained('obat', 'id', 'mutasi_stok_obat_obat_id')
                ->casCadeOnUpdate()->casCadeOnDelete();
            $table->foreignId('depot_id')
                ->constrained('depot', 'id', 'mutasi_stok_obat_depot_id')
                ->casCadeOnUpdate()->casCadeOnDelete();
            $table->string('nomor_batch')->nullable();
            $table->date('tanggal_kadaluarsa');
            $table->unsignedBigInteger('jumlah');
            $table->decimal('harga_beli_satuan', 15, 2)->nullable();
            $table->decimal('harga_jual_umum', 15, 2)->nullable();
            $table->decimal('harga_jual_otc', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_stok_obat_detail');
    }
};
