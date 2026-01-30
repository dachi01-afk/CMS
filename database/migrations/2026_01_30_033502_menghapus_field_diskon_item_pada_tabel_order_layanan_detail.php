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
        // 1. Hapus field diskon_item dari tabel detail
        Schema::table('order_layanan_detail', function (Blueprint $table) {
            if (Schema::hasColumn('order_layanan_detail', 'diskon_item')) {
                $table->dropColumn('diskon_item');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan seperti semula jika rollback
        Schema::table('order_layanan', function (Blueprint $table) {
            $table->dropColumn('diskon');
        });

        Schema::table('order_layanan_detail', function (Blueprint $table) {
            $table->decimal('diskon_item', 12, 2)->default(0)->after('harga_satuan');
        });
    }
};
