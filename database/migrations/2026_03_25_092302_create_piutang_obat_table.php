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
        Schema::create('piutang_obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_obat_id')
                ->constrained('return_obat', 'id', 'piutang_obat_return_obat_id')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId('supplier_id')
                ->constrained('supplier', 'id', 'piutang_obat_supplier_id')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId('dibuat_oleh')
                ->constrained('user', 'id', 'piutang_obat_dibuat_oleh_user_id')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId('diupdate_oleh')->nullable()
                ->constrained('user', 'id', 'piutang_obat_diupdate_oleh_user_id')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId('metode_pembayaran_id')->nullable()
                ->constrained('metode_pembayaran', 'id', 'piutang_obat_metode_pembayaran_id')
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
        Schema::dropIfExists('piutang_obat');
    }
};
