<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom kunjungan_id kalau belum ada
        if (!Schema::hasColumn('dental_examinations', 'kunjungan_id')) {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->unsignedBigInteger('kunjungan_id')
                    ->nullable()
                    ->after('order_layanan_id');
            });
        }

        // 2. Backfill HANYA kalau kolom order_layanan.kunjungan_id memang ada
        if (
            Schema::hasTable('order_layanan') &&
            Schema::hasColumn('order_layanan', 'kunjungan_id')
        ) {
            DB::statement("
                UPDATE dental_examinations de
                JOIN order_layanan ol ON ol.id = de.order_layanan_id
                SET de.kunjungan_id = ol.kunjungan_id
                WHERE de.kunjungan_id IS NULL
                  AND ol.kunjungan_id IS NOT NULL
            ");
        }

        // 3. Tambah index kalau belum ada
        try {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->index('kunjungan_id', 'dental_examinations_kunjungan_id_idx');
            });
        } catch (\Throwable $e) {
        }

        // 4. Tambah foreign key kalau belum ada
        try {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->foreign('kunjungan_id', 'dental_examinations_kunjungan_id_fk')
                    ->references('id')
                    ->on('kunjungan')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            });
        } catch (\Throwable $e) {
        }

        // 5. Tambah unique constraint kalau belum ada
        try {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->unique(['kunjungan_id', 'pasien_id'], 'uniq_dental_kunjungan_pasien');
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        try {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->dropUnique('uniq_dental_kunjungan_pasien');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->dropForeign('dental_examinations_kunjungan_id_fk');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->dropIndex('dental_examinations_kunjungan_id_idx');
            });
        } catch (\Throwable $e) {
        }

        if (Schema::hasColumn('dental_examinations', 'kunjungan_id')) {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->dropColumn('kunjungan_id');
            });
        }
    }
};