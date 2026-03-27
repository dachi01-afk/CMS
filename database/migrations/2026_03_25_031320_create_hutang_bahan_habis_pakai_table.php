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
        Schema::create('hutang_bahan_habis_pakai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restock_bahan_habis_pakai_id')
                ->constrained('restock_bahan_habis_pakai', 'id', 'hutang_bahan_habis_pakai_restock_bahan_habis_pakai_id')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('supplier_id')
                ->constrained('supplier', 'id', 'hutang_bahan_habis_pakai_supplier_id')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('dibuat_oleh')
                ->constrained('user', 'id', 'hutang_bahan_habis_pakai_dibuat_oleh_user_id')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('diupdate_oleh')->nullable()
                ->constrained('user', 'id', 'hutang_bahan_habis_pakai_diupdate_oleh_user_id')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('metode_pembayaran_id')->nullable()
                ->constrained('metode_pembayaran', 'id', 'hutang_bahan_habis_pakai_metode_pembayaran_id')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal_hutang');
            $table->date('tanggal_jatuh_tempo');
            $table->decimal('total_hutang', 18, 2);
            $table->date('tanggal_pelunasan')->nullable();
            $table->string('no_faktur')->unique();
            $table->string('bukti_pembayaran')->nullable();
            $table->enum('status_hutang', ['Belum Lunas', 'Sudah Lunas', 'Dibatalkan'])->default('Belum Lunas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hutang_bahan_habis_pakai');
    }
};
