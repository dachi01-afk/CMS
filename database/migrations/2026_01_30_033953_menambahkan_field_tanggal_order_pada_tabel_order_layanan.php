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
        Schema::table('order_layanan', function (Blueprint $table) {
            // Menambahkan field tanggal_order setelah kode_transaksi
            // Gunakan dateTime agar tercatat jam transaksinya juga
            $table->dateTime('tanggal_order')->nullable()->after('kode_transaksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_layanan', function (Blueprint $table) {
            $table->dropColumn('tanggal_order');
        });
    }
};
