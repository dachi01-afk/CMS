<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi: menambahkan kolom no_hp_pasien.
     */
    public function up(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->string('no_hp_pasien')->nullable()->after('alamat');
        });
    }

    /**
     * Kembalikan migrasi (hapus kolom no_hp_pasien).
     */
    public function down(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->dropColumn('no_hp_pasien');
        });
    }
};
