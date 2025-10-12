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
            $table->id();
            $table->foreignId('emr_id')->constrained('emr', 'id', 'pembayaran_emr_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->decimal('total_tagihan', 8, 2);
            $table->decimal('uang_yang_diterima', 8, 2);
            $table->decimal('kembalian', 8, 2);
            $table->enum('metode_pembayaran', ['Cash', 'Transfer']);
            $table->string('kode_transaksi')->nullable(); // bisa dari Midtrans / bank
            $table->dateTime('tanggal_pembayaran');
            $table->enum('status', ['Sudah Bayar', 'Belum Bayar']);
            // Catatan opsional (misalnya "Pembayaran tunai diterima oleh kasir A")
            $table->text('catatan')->nullable();
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
