<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // FINAL ENUM
    private array $finalEnum = ['Admin', 'Dokter', 'Farmasi', 'Pasien'];

    // LAMA (untuk rollback)
    private array $oldEnum   = ['Admin', 'Dokter', 'Apoteker', 'Pasien'];

    public function up(): void
    {
        // 1) Buka dulu jadi superset: berisi Apoteker + Farmasi
        DB::statement("
            ALTER TABLE `user`
            MODIFY `role` ENUM('Admin','Dokter','Apoteker','Farmasi','Pasien')
            NOT NULL DEFAULT 'Pasien'
        ");

        // 2) Remap data
        DB::table('user')->where('role', 'Apoteker')->update(['role' => 'Farmasi']);

        // 3) Kunci ke final (buang Apoteker)
        DB::statement("
            ALTER TABLE `user`
            MODIFY `role` ENUM('Admin','Dokter','Farmasi','Pasien')
            NOT NULL DEFAULT 'Pasien'
        ");
    }

    public function down(): void
    {
        // 1) Buka ke superset dulu
        DB::statement("
            ALTER TABLE `user`
            MODIFY `role` ENUM('Admin','Dokter','Apoteker','Farmasi','Pasien')
            NOT NULL DEFAULT 'Pasien'
        ");

        // 2) Balikkan data Farmasi -> Apoteker
        DB::table('user')->where('role', 'Farmasi')->update(['role' => 'Apoteker']);

        // 3) Kunci ke definisi lama
        DB::statement("
            ALTER TABLE `user`
            MODIFY `role` ENUM('Admin','Dokter','Apoteker','Pasien')
            NOT NULL DEFAULT 'Pasien'
        ");
    }
};
