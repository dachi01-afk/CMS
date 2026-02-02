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
        Schema::table('bahan_habis_pakai', function (Blueprint $table) {
            // Menghapus field yang sekarang dikelola di tabel batch_obat
            $table->dropColumn([
                'tanggal_kadaluarsa_bhp',
                'no_batch'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_habis_pakai', function (Blueprint $table) {
            $table->date('tanggal_kadaluarsa_bhp')->nullable();
            $table->string('no_batch')->nullable();
        });
    }
};
