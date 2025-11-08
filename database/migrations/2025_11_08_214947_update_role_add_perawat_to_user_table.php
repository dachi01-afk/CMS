<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Jalankan migrasi (menambahkan enum 'Perawat')
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE user MODIFY role ENUM('Admin', 'Dokter', 'Farmasi', 'Perawat', 'Kasir', 'Pasien')");
    }

    /**
     * Rollback migrasi (hapus enum 'Perawat')
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE user MODIFY role ENUM('Admin', 'Dokter', 'Farmasi', 'Pasien') NOT NULL");
    }
};
