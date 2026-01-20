<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('depot_bhp', function (Blueprint $table) {
            // 1. Hapus foreign key yang lama
            // Gunakan nama index yang kamu definisikan sebelumnya
            $table->dropForeign('depot_bhp_bahan_habis_pakai_id');

            // 2. Buat kembali foreign key dengan tambahan onDelete('cascade')
            $table->foreign('bahan_habis_pakai_id', 'depot_bhp_bahan_habis_pakai_id')
                ->references('id')
                ->on('bahan_habis_pakai')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('depot_bhp', function (Blueprint $table) {
            // Untuk rollback: kembalikan ke kondisi tanpa onDelete('cascade')
            $table->dropForeign('depot_bhp_bahan_habis_pakai_id');

            $table->foreign('bahan_habis_pakai_id', 'depot_bhp_bahan_habis_pakai_id')
                ->references('id')
                ->on('bahan_habis_pakai');
        });
    }
};
