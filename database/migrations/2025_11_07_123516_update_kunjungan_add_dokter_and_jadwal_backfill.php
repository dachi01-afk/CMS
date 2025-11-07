<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kunjungan', function (Blueprint $table) {
            // Tambahkan kolom baru (sementara nullable agar bisa diisi dulu)
            if (!Schema::hasColumn('kunjungan', 'jadwal_dokter_id')) {
                $table->foreignId('jadwal_dokter_id')
                    ->nullable()
                    ->after('id');
            }

            if (!Schema::hasColumn('kunjungan', 'dokter_id')) {
                $table->foreignId('dokter_id')
                    ->nullable()
                    ->after('jadwal_dokter_id');
            }
        });

        /**
         * 🔁 BACKFILL DATA LAMA
         * Logika: cari dokter_id dari tabel jadwal_dokter yang memiliki poli_id sama.
         * Kalau ada lebih dari satu dokter dalam poli itu, ambil yang pertama (terbaru).
         */
        DB::statement("
            UPDATE kunjungan k
            JOIN (
                SELECT poli_id, MIN(id) AS jadwal_id, MIN(dokter_id) AS dokter_id
                FROM jadwal_dokter
                GROUP BY poli_id
            ) jd ON jd.poli_id = k.poli_id
            SET 
                k.jadwal_dokter_id = jd.jadwal_id,
                k.dokter_id = jd.dokter_id
            WHERE k.dokter_id IS NULL
        ");

        // Setelah data terisi, pasang constraint & index
        Schema::table('kunjungan', function (Blueprint $table) {
            $table->foreign('jadwal_dokter_id')
                ->references('id')->on('jadwal_dokter')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('dokter_id')
                ->references('id')->on('dokter')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(['poli_id', 'tanggal_kunjungan'], 'idx_kunjungan_poli_tanggal');
            $table->index('status', 'idx_kunjungan_status');
        });
    }

    public function down(): void
    {
        Schema::table('kunjungan', function (Blueprint $table) {
            if (Schema::hasColumn('kunjungan', 'jadwal_dokter_id')) {
                $table->dropForeign(['jadwal_dokter_id']);
                $table->dropColumn('jadwal_dokter_id');
            }

            if (Schema::hasColumn('kunjungan', 'dokter_id')) {
                $table->dropForeign(['dokter_id']);
                $table->dropColumn('dokter_id');
            }

            $table->dropIndex('idx_kunjungan_poli_tanggal');
            $table->dropIndex('idx_kunjungan_status');
        });
    }
};
