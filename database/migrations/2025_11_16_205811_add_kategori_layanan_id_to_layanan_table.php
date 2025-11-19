<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layanan', function (Blueprint $table) {

            // Tambah kolom kategori_layanan_id
            // Nullable dulu supaya aman untuk production (data lama tetap valid)
            $table->foreignId('kategori_layanan_id')
                ->nullable()
                ->after('id')
                ->constrained('kategori_layanan', 'id', 'layanan_kategori_layanan_id')
                ->nullOnDelete()      // jika kategori dihapus, set NULL
                ->cascadeOnUpdate();  // jika pindah ID kategori, update otomatis
        });
    }

    public function down(): void
    {
        Schema::table('layanan', function (Blueprint $table) {

            // Drop foreign key + kolom nya
            $table->dropForeign(['kategori_layanan_id']);
            $table->dropColumn('kategori_layanan_id');
        });
    }
};
