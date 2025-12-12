<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Tambah kolom status_farmasi ke tabel resep
        Schema::table('resep', function (Blueprint $table) {
            if (!Schema::hasColumn('resep', 'status')) {
                $table->enum('status', ['waiting', 'done'])
                    ->default('waiting')
                    ->after('kunjungan_id');
            }
        });

        // 2) Backfill data dari resep_obat.status (Belum Diambil/Sudah Diambil)
        //    Logika:
        //    - Jika MASIH ADA item "Belum Diambil" => waiting
        //    - Jika TIDAK ADA "Belum Diambil" tapi ADA "Sudah Diambil" => done
        //    - Selain itu => waiting
        if (Schema::hasTable('resep_obat') && Schema::hasColumn('resep_obat', 'status')) {
            DB::statement("
                UPDATE resep r
                SET r.status =
                    CASE
                        WHEN EXISTS (
                            SELECT 1 FROM resep_obat ro
                            WHERE ro.resep_id = r.id
                              AND ro.status = 'Belum Diambil'
                        ) THEN 'waiting'
                        WHEN EXISTS (
                            SELECT 1 FROM resep_obat ro
                            WHERE ro.resep_id = r.id
                              AND ro.status = 'Sudah Diambil'
                        ) THEN 'done'
                        ELSE 'waiting'
                    END
            ");
        }

        // 3) Hapus kolom status dari resep_obat (karena dipindahkan ke resep)
        Schema::table('resep_obat', function (Blueprint $table) {
            if (Schema::hasColumn('resep_obat', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    public function down(): void
    {
        // Balikkan: tambah lagi status di resep_obat
        Schema::table('resep_obat', function (Blueprint $table) {
            if (!Schema::hasColumn('resep_obat', 'status')) {
                $table->enum('status', ['Belum Diambil', 'Sudah Diambil'])
                    ->default('Belum Diambil')
                    ->after('keterangan'); // sesuaikan kalau posisi kamu beda
            }
        });

        // Hapus status_farmasi dari resep
        Schema::table('resep', function (Blueprint $table) {
            if (Schema::hasColumn('resep', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
