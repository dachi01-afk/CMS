<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            // Tambah value 'Kasir' ke ENUM role
            // ENUM final: Admin, Dokter, Apoteker, Pasien, Kasir
            if (DB::getDriverName() === 'mysql') {
                DB::statement("
                ALTER TABLE `user`
                MODIFY `role`
                ENUM('Admin','Dokter','Farmasi','Pasien','Kasir')
                NOT NULL
            ");
            } else {
                // Jika bukan MySQL, biar jelas error terangkat (atau ganti sesuai catatan PostgreSQL di bawah)
                throw new \RuntimeException('This migration currently supports MySQL/MariaDB only.');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            //
        });
    }
};
