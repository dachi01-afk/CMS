<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1️⃣ TABEL KUNJUNGAN — keluhan_awal -> nullable
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('kunjungan', 'keluhan_awal')) {
            DB::statement('ALTER TABLE kunjungan MODIFY keluhan_awal TEXT NULL');
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ TABEL RESEP — kunjungan_id -> nullable
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('resep', 'kunjungan_id')) {

            // 🔍 Cari nama constraint foreign key yang sebenarnya
            $fkName = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'resep'
                AND COLUMN_NAME = 'kunjungan_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            // 🧩 Drop FK kalau ada
            if ($fkName) {
                Schema::table('resep', function (Blueprint $table) use ($fkName) {
                    $table->dropForeign($fkName->CONSTRAINT_NAME);
                });
            }

            // 🔄 Ubah kolom jadi nullable (pakai SQL langsung biar aman)
            DB::statement('ALTER TABLE resep MODIFY kunjungan_id BIGINT UNSIGNED NULL');

            // 🔗 Tambahkan ulang foreign key dengan nama konsisten
            Schema::table('resep', function (Blueprint $table) {
                $table->foreign('kunjungan_id', 'resep_kunjungan_id_fk')
                    ->references('id')
                    ->on('kunjungan')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ TABEL RESEP_OBAT — dosis & keterangan -> nullable
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('resep_obat', 'dosis')) {
            DB::statement('ALTER TABLE resep_obat MODIFY dosis DECIMAL(8,2) NULL');
        }

        if (Schema::hasColumn('resep_obat', 'keterangan')) {
            DB::statement('ALTER TABLE resep_obat MODIFY keterangan VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ROLLBACK: Kembalikan ke NOT NULL seperti semula
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('kunjungan', 'keluhan_awal')) {
            DB::statement('ALTER TABLE kunjungan MODIFY keluhan_awal TEXT NOT NULL');
        }

        if (Schema::hasColumn('resep', 'kunjungan_id')) {

            // cari nama constraint yang aktif sekarang
            $fkName = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'resep'
                AND COLUMN_NAME = 'kunjungan_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            if ($fkName) {
                Schema::table('resep', function (Blueprint $table) use ($fkName) {
                    $table->dropForeign($fkName->CONSTRAINT_NAME);
                });
            }

            DB::statement('ALTER TABLE resep MODIFY kunjungan_id BIGINT UNSIGNED NOT NULL');

            Schema::table('resep', function (Blueprint $table) {
                $table->foreign('kunjungan_id', 'resep_kunjungan_id_fk')
                    ->references('id')
                    ->on('kunjungan')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            });
        }

        if (Schema::hasColumn('resep_obat', 'dosis')) {
            DB::statement('ALTER TABLE resep_obat MODIFY dosis DECIMAL(8,2) NOT NULL');
        }

        if (Schema::hasColumn('resep_obat', 'keterangan')) {
            DB::statement('ALTER TABLE resep_obat MODIFY keterangan VARCHAR(255) NOT NULL');
        }
    }
};
