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
            // 1. Hapus Foreign Key Constraint terlebih dahulu
            // Biasanya format namanya: nama_tabel_nama_kolom_foreign
            $table->dropForeign('order_layanan_poli_id');
            $table->dropForeign('order_layanan_dokter_id');
            $table->dropForeign('order_layanan_jadwal_dokter_id');

            // 2. Baru kemudian hapus kolomnya
            $table->dropColumn(['poli_id', 'dokter_id', 'jadwal_dokter_id', 'keluhan_utama']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_layanan', function (Blueprint $table) {
            $table->bigInteger('poli_id')->after('pasien_id');
            $table->bigInteger('dokter_id')->nullable()->after('poli_id');
            $table->bigInteger('jadwal_dokter_id')->nullable()->after('dokter_id');
            $table->text('keluhan_utama')->nullable()->after('jadwal_dokter_id');
        });
    }
};
