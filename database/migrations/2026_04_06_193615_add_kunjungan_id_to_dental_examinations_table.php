<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stop kalau table dental_examinations belum ada
        if (! Schema::hasTable('dental_examinations')) {
            return;
        }

        // 1. Tambah kolom kunjungan_id kalau belum ada
        if (! Schema::hasColumn('dental_examinations', 'kunjungan_id')) {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->unsignedBigInteger('kunjungan_id')
                    ->nullable()
                    ->after('order_layanan_id');
            });
        }

        // 2. Backfill HANYA kalau table dan kolom pendukung memang ada
        if (
            Schema::hasTable('order_layanan') &&
            Schema::hasColumn('order_layanan', 'kunjungan_id') &&
            Schema::hasColumn('dental_examinations', 'order_layanan_id') &&
            Schema::hasColumn('dental_examinations', 'kunjungan_id')
        ) {
            DB::statement('
                UPDATE dental_examinations de
                JOIN order_layanan ol ON ol.id = de.order_layanan_id
                SET de.kunjungan_id = ol.kunjungan_id
                WHERE de.kunjungan_id IS NULL
                  AND ol.kunjungan_id IS NOT NULL
            ');
        }

        // 3. Tambah index
        try {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->index('kunjungan_id', 'dental_examinations_kunjungan_id_idx');
            });
        } catch (\Throwable $e) {
            // Abaikan kalau index sudah ada
        }

        // 4. Tambah foreign key kalau table kunjungan ada
        if (Schema::hasTable('kunjungan')) {
            try {
                Schema::table('dental_examinations', function (Blueprint $table) {
                    $table->foreign('kunjungan_id', 'dental_examinations_kunjungan_id_fk')
                        ->references('id')
                        ->on('kunjungan')
                        ->cascadeOnUpdate()
                        ->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // Abaikan kalau foreign key sudah ada
            }
        }

        // 5. Tambah unique constraint kalau kolom pasien_id ada
        if (Schema::hasColumn('dental_examinations', 'pasien_id')) {
            try {
                Schema::table('dental_examinations', function (Blueprint $table) {
                    $table->unique(['kunjungan_id', 'pasien_id'], 'uniq_dental_kunjungan_pasien');
                });
            } catch (\Throwable $e) {
                // Abaikan kalau unique sudah ada
            }
        }
    }

    public function down(): void
    {
        // Stop kalau table dental_examinations belum ada
        if (! Schema::hasTable('dental_examinations')) {
            return;
        }

        try {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->dropUnique('uniq_dental_kunjungan_pasien');
            });
        } catch (\Throwable $e) {
            // Abaikan kalau unique tidak ada
        }

        try {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->dropForeign('dental_examinations_kunjungan_id_fk');
            });
        } catch (\Throwable $e) {
            // Abaikan kalau foreign key tidak ada
        }

        try {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->dropIndex('dental_examinations_kunjungan_id_idx');
            });
        } catch (\Throwable $e) {
            // Abaikan kalau index tidak ada
        }

        if (Schema::hasColumn('dental_examinations', 'kunjungan_id')) {
            Schema::table('dental_examinations', function (Blueprint $table) {
                $table->dropColumn('kunjungan_id');
            });
        }
    }
};
