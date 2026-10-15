<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menambahkan enum 'Auditor' pada kolom user.role.
     * Mengikuti pola migration role sebelumnya (raw ALTER karena ENUM MySQL).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE user MODIFY role ENUM('Super Admin','Admin', 'Dokter', 'Farmasi', 'Perawat', 'Kasir', 'Pasien', 'Auditor')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE user MODIFY role ENUM('Super Admin','Admin', 'Dokter', 'Farmasi', 'Perawat', 'Kasir', 'Pasien')");
    }
};
