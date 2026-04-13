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
        Schema::table('penjualan_obat_detail', function (Blueprint $table) {
            $table->enum('diskon_tipe', ['persen', 'nomial'])->nullable()->after('sub_total');
            $table->decimal('diskon_nilai', 10, 2)->nullable()->after('diskon_tipe');
            $table->decimal('total_setelah_diskon', 10, 2)->nullable()->after('diskon_nilai');
            $table->decimal('uang_yang_diterima', 10, 2)->nullable()->after('total_setelah_diskon');
            $table->decimal('kembalian', 10, 2)->nullable()->after('uang_yang_diterima');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan_obat_detail', function (Blueprint $table) {
            //
        });
    }
};
