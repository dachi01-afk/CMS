<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resume_dokter', function (Blueprint $table) {
            $table->id();
            // PK unik untuk tiap resume dokter

            $table->foreignId('emr_id')
                ->constrained('emr')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            // FK ke tabel emr: resume ini milik EMR mana
            // cascade delete: kalau EMR dihapus, resume ikut terhapus (biar tidak nyangkut)

            $table->unique('emr_id');
            // memastikan 1 EMR cuma punya 1 resume (relasi 1-1)

            $table->foreignId('dokter_id')
                ->nullable()
                ->constrained('dokter')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            // dokter yang buat/menandatangani resume
            // nullable: aman kalau suatu saat resume dibuat sistem/admin
            // nullOnDelete: kalau dokter dihapus, resume tetap ada tapi dokter_id jadi NULL

            $table->text('ringkasan_kasus')->nullable();
            // ringkasan klinis: keluhan, temuan penting, perjalanan kasus secara singkat

            $table->text('diagnosis_utama')->nullable();
            // diagnosis utama (boleh snapshot dari emr.diagnosis)

            $table->text('diagnosis_sekunder')->nullable();
            // diagnosis penyerta/komorbid (misal: DM, hipertensi, dll)

            $table->text('tindakan')->nullable();
            // tindakan/prosedur yang dilakukan (misal nebulizer, jahit luka, dsb)

            $table->text('terapi_ringkas')->nullable();
            // ringkasan terapi/obat (detail obat tetap bisa ambil dari resep + resep_obat)

            $table->text('hasil_penunjang_ringkas')->nullable();
            // ringkasan hasil lab/radiologi yang penting (detail tetap ada di tes_lab)

            $table->string('kondisi_akhir')->nullable();
            // kondisi saat selesai/pulang (mis: membaik, stabil, dirujuk, dll)

            $table->text('instruksi_pulang')->nullable();
            // edukasi/instruksi untuk pasien: aturan obat, diet, warning sign, dll

            $table->text('rencana_tindak_lanjut')->nullable();
            // follow up: kontrol kapan, rujuk kemana, pemeriksaan lanjutan, dsb

            $table->enum('status', ['draft', 'final'])->default('draft');
            // draft = boleh diedit, final = sudah dikunci/dianggap resmi

            $table->timestamp('finalized_at')->nullable();
            // kapan resume difinalisasi (dibutuhkan untuk audit & legal)

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_dokter');
    }
};
