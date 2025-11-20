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
        Schema::table('pembayaran', function (Blueprint $table) {
            // Jenis diskon: persen / nominal
            $table->enum('diskon_tipe', ['persen', 'nominal'])
                ->nullable()
                ->after('total_tagihan');

            // Nilai diskon (kalau persen, misal 10; kalau nominal, misal 25000)
            $table->decimal('diskon_nilai', 10, 2)
                ->default(0)
                ->after('diskon_tipe');

            // Total yang harus dibayar setelah diskon
            $table->decimal('total_setelah_diskon', 10, 2)
                ->nullable()
                ->after('diskon_nilai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn(['diskon_tipe', 'diskon_nilai', 'total_setelah_diskon']);
        });
    }
};
