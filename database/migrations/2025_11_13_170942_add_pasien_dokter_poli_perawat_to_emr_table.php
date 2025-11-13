<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Tambah kolom baru dulu (nullable supaya aman di data lama)
        Schema::table('emr', function (Blueprint $table) {
            // urutannya: kunjungan_id -> pasien_id -> dokter_id -> poli_id
            $table->foreignId('pasien_id')->nullable()->after('kunjungan_id');
            $table->foreignId('dokter_id')->nullable()->after('pasien_id');
            $table->foreignId('poli_id')->nullable()->after('dokter_id');
            $table->foreignId('perawat_id')->nullable()->after('poli_id');
        });

        // 2) Timpakan data dari tabel kunjungan (snapshot)
        //    Asumsi: di tabel kunjungan sudah ada: pasien_id, dokter_id, poli_id
        DB::statement("
            UPDATE emr e
            JOIN kunjungan k ON k.id = e.kunjungan_id
            SET
                e.pasien_id = k.pasien_id,
                e.dokter_id = k.dokter_id,
                e.poli_id   = k.poli_id
        ");

        // 3) Baru pasang foreign key
        Schema::table('emr', function (Blueprint $table) {
            $table->foreign('pasien_id', 'emr_pasien_id')
                ->references('id')->on('pasien')
                ->cascadeOnUpdate()
                ->nullOnDelete(); // kalau pasien dihapus, set null

            $table->foreign('dokter_id', 'emr_dokter_id')
                ->references('id')->on('dokter')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('poli_id', 'emr_poli_id')
                ->references('id')->on('poli')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreign('perawat_id', 'emr_perawat_id')
                ->references('id')->on('perawat')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('emr', function (Blueprint $table) {
            $table->dropForeign('emr_pasien_id_fk');
            $table->dropForeign('emr_dokter_id_fk');
            $table->dropForeign('emr_poli_id_fk');

            $table->dropColumn(['pasien_id', 'dokter_id', 'poli_id']);
        });
    }
};
