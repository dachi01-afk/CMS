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
            DB::statement("ALTER TABLE user MODIFY role ENUM('Super Admin','Admin', 'Dokter', 'Farmasi', 'Perawat', 'Kasir', 'Pasien')");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            DB::statement("ALTER TABLE user MODIFY role ENUM('Admin', 'Dokter', 'Farmasi', 'Perawat', 'Kasir', 'Pasien)");
        });
    }
};
