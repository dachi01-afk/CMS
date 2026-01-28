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
        Schema::create('mutasi_stok_obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')
                ->constrained('supplier', 'id', 'mutasi_stok_obat_supplier_id')
                ->casCadeOnUpdate()->casCadeOnDelete();
            $table->foreignId('farmasi_id')
                ->constrained('farmasi', 'id', 'mutasi_stok_obat_farmasi_id')
                ->casCadeOnUpdate()->casCadeOnDelete();
            $table->string('nomor_transaksi')->unique();
            $table->string('nomor_faktur')->nullable();
            $table->enum('jenis_transaksi', ['RESTOCK', 'RETURN']);
            $table->date('tanggal_transaksi');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_stok_obat');
    }
};
