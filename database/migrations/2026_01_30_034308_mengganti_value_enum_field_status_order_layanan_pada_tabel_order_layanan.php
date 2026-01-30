<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Opsional: Update data lama agar tidak error saat enum diubah
        // Menyesuaikan status lama ke status baru (misal: semua dianggap Belum Bayar)
        DB::table('order_layanan')->update(['status_order_layanan' => 'Belum Bayar']);

        Schema::table('order_layanan', function (Blueprint $table) {
            // Mengubah tipe data enum menjadi hanya dua pilihan
            $table->enum('status_order_layanan', ['Sudah Bayar', 'Belum Bayar'])
                ->default('Belum Bayar')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_layanan', function (Blueprint $table) {
            // Jika rollback, kita kembalikan ke struktur awal (sesuai gambar image_d321e1.jpg)
            $table->enum('status_order_layanan', ['Draf', 'Dipesan', 'Sudah Datang', 'Menunggu Pembayaran', 'Selesai'])
                ->default('Draf')
                ->change();
        });
    }
};
