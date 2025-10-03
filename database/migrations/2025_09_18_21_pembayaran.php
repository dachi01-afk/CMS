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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('id_pembayaran');
            $table->foreignId('kunjungan_id')->constrained('kunjungan', 'id_kunjungan')->cascadeOnDelete();
            $table->dateTime('tanggal_pembayaran');
            $table->decimal('total_biaya', 10, 2);
            $table->dateTime('waktu_obat_diserahkan')->nullable();
            $table->enum('metode_transaksi', ['Tunai', 'Kartu Debit', 'Kartu Kredit', 'Transfer Bank', 'Virtual Account', 'Lainnya'])->default('Tunai');
            $table->string('nomor_referensi_bank', 100)->nullable();
            $table->string('nama_bank', 50)->nullable();
            $table->string('jenis_kartu', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
