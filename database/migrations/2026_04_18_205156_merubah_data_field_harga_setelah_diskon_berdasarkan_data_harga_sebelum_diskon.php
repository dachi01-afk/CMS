<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<SQL
            UPDATE layanan
            SET harga_setelah_diskon = harga_sebelum_diskon
            WHERE diskon IS NULL OR diskon = 0.00
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak di-rollback karena ini data migration.
        // Mengembalikan nilai sebelumnya tidak aman tanpa backup/snapshot data lama.
    }
};
