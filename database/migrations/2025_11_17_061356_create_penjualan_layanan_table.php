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
        Schema::create('penjualan_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')
                ->nullable()
                ->constrained('pasien', 'id', 'penjualan_layanan_pasien_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('layanan_id')
                ->nullable()
                ->constrained('layanan', 'id', 'penjualan_layanan_layanan_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('kategori_layanan_id')
                ->nullable()
                ->constrained('kategori_layanan', 'id', 'penjualan_layanan_kategori_layanan_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('kunjungan_id')
                ->nullable()
                ->constrained('kunjungan', 'id', 'penjualan_layanan_kunjungan_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('metode_pembayaran_id')
                ->nullable()
                ->constrained('metode_pembayaran', 'id', 'penjualan_layanan_metode_pembayaran_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('kode_transaksi')->nullable();
            $table->integer('jumlah')->default(1);
            $table->decimal('total_tagihan', 15, 2)->nullable();
            $table->decimal('uang_yang_diterima', 15, 2)->nullable();
            $table->decimal('kembalian', 15, 2)->nullable();
            $table->decimal('sub_total', 15, 2)->nullable();
            $table->dateTime('tanggal_transaksi')->nullable();
            $table->string('bukti_pembayaran')->nullable();
            $table->enum('status', ['Sudah Bayar', 'Belum Bayar'])->default('Belum Bayar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_layanan');
    }
};
