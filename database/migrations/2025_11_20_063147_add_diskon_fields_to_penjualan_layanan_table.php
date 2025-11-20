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
        Schema::table('penjualan_layanan', function (Blueprint $table) {
            // total_tagihan sudah ada → kita tambahkan field diskon di dekat situ

            // tipe diskon: persen / rupiah (boleh null kalau tidak pakai diskon)
            $table->enum('diskon_tipe', ['persen', 'rupiah'])
                ->nullable()
                ->after('total_tagihan');
            
            // nilai diskon (misal 10 untuk 10% atau 15000 untuk 15.000 rupiah)
            $table->decimal('diskon_nilai', 15, 2)
                ->default(0)
                ->after('diskon_tipe');

            // total setelah diskon (grand total yang benar-benar harus dibayar)
            $table->decimal('total_setelah_diskon', 15, 2)
                ->nullable()
                ->after('diskon_nilai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan_layanan', function (Blueprint $table) {
            $table->dropColumn([
                'diskon_tipe',
                'diskon_nilai',
                'total_setelah_diskon',
            ]);
        });
    }
};
