<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Step 1: Tambah kolom kunjungan_id
        Schema::table('dental_examinations', function (Blueprint $table) {
            $table->unsignedBigInteger('kunjungan_id')
                ->nullable()
                ->after('order_layanan_id');
        });

        // ✅ Step 2: Backfill data (isi kunjungan_id dari order_layanan)
        // Ambil kunjungan_id dari tabel order_layanan
        DB::statement("
            UPDATE dental_examinations de
            JOIN order_layanan ol ON ol.id = de.order_layanan_id
            SET de.kunjungan_id = ol.kunjungan_id
            WHERE de.kunjungan_id IS NULL
              AND ol.kunjungan_id IS NOT NULL
        ");

        // ✅ Step 3: Tambah foreign key & unique constraint
        Schema::table('dental_examinations', function (Blueprint $table) {
            // Index untuk performa
            $table->index('kunjungan_id', 'dental_examinations_kunjungan_id_idx');

            // Foreign key
            $table->foreign('kunjungan_id', 'dental_examinations_kunjungan_id_fk')
                ->references('id')->on('kunjungan')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // ✅ Unique constraint: 1 kunjungan = 1 dental exam
            // Cegah duplikasi dengan try-catch
            try {
                $table->unique(['kunjungan_id', 'pasien_id'], 'uniq_dental_kunjungan_pasien');
            } catch (\Exception $e) {
                // Kalau unique constraint sudah ada, skip
            }
        });
    }

    public function down(): void
    {
        Schema::table('dental_examinations', function (Blueprint $table) {
            // Drop constraints & indexes dulu
            try {
                $table->dropUnique('uniq_dental_kunjungan_pasien');
            } catch (\Exception $e) {}

            try {
                $table->dropForeign('dental_examinations_kunjungan_id_fk');
            } catch (\Exception $e) {}

            try {
                $table->dropIndex('dental_examinations_kunjungan_id_idx');
            } catch (\Exception $e) {}

            // Baru drop kolom
            $table->dropColumn('kunjungan_id');
        });
    }
};