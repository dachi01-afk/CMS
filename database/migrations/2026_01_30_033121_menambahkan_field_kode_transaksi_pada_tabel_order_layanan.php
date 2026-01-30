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
        Schema::table('order_layanan', function (Blueprint $blueprint) {
            // Kita pasang 'after' agar rapi di urutan kolom database
            // unique() supaya tidak ada kode transaksi yang kembar
            $blueprint->string('kode_transaksi', 50)->unique()->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_layanan', function (Blueprint $blueprint) {
            $blueprint->dropColumn('kode_transaksi');
        });
    }
};
