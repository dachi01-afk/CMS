<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom diskon ke tabel penjualan_obat.
     */
    public function up(): void
    {
        Schema::table('penjualan_obat', function (Blueprint $table) {
            // tipe diskon: persen / nominal
            $table->enum('diskon_tipe', ['persen', 'nominal'])
                ->nullable()
                ->after('total_tagihan');

            // nilai diskon: bisa persen (misal 10.00) atau nominal (misal 15000.00)
            $table->decimal('diskon_nilai', 10, 2)
                ->nullable()
                ->after('diskon_tipe');

            // total setelah diskon, untuk catat nominal akhir yang harus dibayar
            $table->decimal('total_setelah_diskon', 15, 2)
                ->nullable()
                ->after('diskon_nilai');
        });
    }

    /**
     * Rollback: hapus kolom diskon dari tabel penjualan_obat.
     */
    public function down(): void
    {
        Schema::table('penjualan_obat', function (Blueprint $table) {
            $table->dropColumn([
                'diskon_tipe',
                'diskon_nilai',
                'total_setelah_diskon',
            ]);
        });
    }
};
