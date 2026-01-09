<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layanan', function (Blueprint $table) {

            // rename field
            $table->renameColumn('harga_layanan', 'harga_sebelum_diskon');

            // tambah field baru
            $table->decimal('diskon', 12, 2)->default(0)->after('harga_sebelum_diskon');
            $table->decimal('harga_setelah_diskon', 12, 2)->after('diskon');

            // layanan bisa dipakai semua poli (global)
            $table->boolean('is_global')->default(false)->after('harga_setelah_diskon');
        });
    }

    public function down(): void
    {
        Schema::table('layanan', function (Blueprint $table) {

            // kembalikan ke kondisi awal
            $table->renameColumn('harga_sebelum_diskon', 'harga_layanan');

            $table->dropColumn([
                'diskon',
                'harga_setelah_diskon',
                'is_global',
            ]);
        });
    }
};
