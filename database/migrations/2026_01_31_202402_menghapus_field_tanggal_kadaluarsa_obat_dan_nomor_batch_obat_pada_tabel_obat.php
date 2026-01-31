<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk menghapus field yang sudah dipindahkan ke tabel batch.
     */
    public function up(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            // Menghapus field yang sekarang dikelola di tabel batch_obat
            $table->dropColumn([
                'tanggal_kadaluarsa_obat', // Sudah ada di batch_obat
                'nomor_batch_obat'         // Sudah ada di batch_obat
            ]);
        });
    }

    /**
     * Mengembalikan field jika migrasi di-rollback.
     */
    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->date('tanggal_kadaluarsa_obat')->nullable();
            $table->string('nomor_batch_obat')->nullable();
        });
    }
};
