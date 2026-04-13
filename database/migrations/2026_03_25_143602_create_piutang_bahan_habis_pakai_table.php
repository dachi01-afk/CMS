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
        Schema::create('piutang_bahan_habis_pakai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_bahan_habis_pakai_id')
                ->constrained('return_bahan_habis_pakai', 'id', 'piutang_bahan_habis_pakai_return_bahan_habis_pakai_id')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId('supplier_id')
                ->constrained('supplier', 'id', 'piutang_bahan_habis_pakai_supplier_id')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId('dibuat_oleh')
                ->constrained('user', 'id', 'piutang_bahan_habis_pakai_dibuat_oleh_user_id')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId('diupdate_oleh')->nullable()
                ->constrained('user', 'id', 'piutang_bahan_habis_pakai_diupdate_oleh_user_id')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId('metode_pembayaran_id')->nullable()
                ->constrained('metode_pembayaran', 'id', 'piutang_bahan_habis_pakai_metode_pembayaran_id')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->date('tanggal_piutang');
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->decimal('total_piutang', 18, 2);
            $table->date('tanggal_pelunasan')->nullable();

            $table->string('no_referensi')->unique(); // nomor retur / nomor klaim
            $table->string('bukti_penerimaan')->nullable();

            $table->enum('status_piutang', ['Belum Lunas', 'Sudah Lunas'])->default('Belum Lunas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piutang_bahan_habis_pakai');
    }
};
