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
            $table->foreignId('emr_id')
                ->constrained('emr')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('total_tagihan', 10, 2)->nullable(); // Ubah jadi 10,2 untuk nilai lebih besar
            $table->decimal('uang_yang_diterima', 10, 2)->nullable();
            $table->decimal('kembalian', 10, 2)->nullable();

            // ✅ FOREIGN KEY KE METODE_PEMBAYARAN (untuk fitur baru)
            $table->foreignId('metode_pembayaran_id')
                ->nullable() // ⚠️ PENTING: HARUS NULLABLE
                ->constrained('metode_pembayaran', 'id', 'pembayaran_metode_pembayaran_id')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('kode_transaksi')->nullable();
            $table->dateTime('tanggal_pembayaran')->nullable();
            $table->enum('status', ['Sudah Bayar', 'Belum Bayar'])->default('Belum Bayar');
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
