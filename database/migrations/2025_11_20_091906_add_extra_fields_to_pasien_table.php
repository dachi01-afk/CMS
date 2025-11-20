<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom-kolom pendukung ke tabel pasien.
     */
    public function up(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            // Identitas tambahan
            $table->string('nik', 20)
                ->nullable()
                ->after('no_emr');

            $table->string('no_bpjs', 50)
                ->nullable()
                ->after('nik');

            // Info klinis ringan
            $table->string('golongan_darah', 3)
                ->nullable()
                ->after('jenis_kelamin');

            $table->string('status_perkawinan', 20)
                ->nullable()
                ->after('golongan_darah');

            $table->string('pekerjaan', 100)
                ->nullable()
                ->after('status_perkawinan');

            // Penanggung jawab
            $table->string('nama_penanggung_jawab', 255)
                ->nullable()
                ->after('no_hp_pasien');

            $table->string('no_hp_penanggung_jawab', 20)
                ->nullable()
                ->after('nama_penanggung_jawab');

            // Catatan medis singkat
            $table->text('alergi')
                ->nullable()
                ->after('no_hp_penanggung_jawab');

            // Optional: barcode terpisah dari QR
            $table->string('barcode_pasien', 255)
                ->nullable()
                ->after('qr_code_pasien');
        });
    }

    /**
     * Rollback perubahan.
     */
    public function down(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->dropColumn([
                'nik',
                'no_bpjs',
                'golongan_darah',
                'status_perkawinan',
                'pekerjaan',
                'nama_penanggung_jawab',
                'no_hp_penanggung_jawab',
                'alergi',
                'barcode_pasien',
            ]);
        });
    }
};
