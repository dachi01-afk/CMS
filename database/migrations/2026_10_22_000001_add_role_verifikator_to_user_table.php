<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menambahkan role 'Verifikator' pada kolom user.role.
     * Role ini bertugas menyetujui atau menolak pengajuan edit/hapus data
     * yang dikirim oleh Admin dan Farmasi.
     *
     * Mengikuti pola migration role sebelumnya (raw ALTER karena ENUM MySQL).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE user MODIFY role ENUM('Super Admin','Admin', 'Dokter', 'Farmasi', 'Perawat', 'Kasir', 'Pasien', 'Auditor', 'Verifikator')");
    }

    public function down(): void
    {
        DB::table('user')->where('role', 'Verifikator')->update(['role' => 'Admin']);

        DB::statement("ALTER TABLE user MODIFY role ENUM('Super Admin','Admin', 'Dokter', 'Farmasi', 'Perawat', 'Kasir', 'Pasien', 'Auditor')");
    }
};
