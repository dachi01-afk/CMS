<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_examinations', function (Blueprint $table) {
            $table->id();

            // ============================
            // RELASI UTAMA
            // ============================
            $table->unsignedBigInteger('pasien_id');
            $table->unsignedBigInteger('order_layanan_id')->nullable();

            // ============================
            // SECTION 1: INFORMASI DASAR
            // ============================
            $table->date('tanggal_kunjungan');
            $table->string('dpjp_nama')->nullable()->comment('Dokter Penanggung Jawab Pelayanan');
            $table->string('ppjp_nama')->nullable()->comment('Penanggung Jawab Pasien');

            // ============================
            // SECTION 2: ODONTOGRAM (GIGI)
            // ============================
            $table->json('gigi_dewasa_atas')->nullable()->comment('Gigi 18-28');
            $table->json('gigi_dewasa_bawah')->nullable()->comment('Gigi 48-38');
            $table->json('gigi_anak_atas')->nullable()->comment('Gigi 55-65');
            $table->json('gigi_anak_bawah')->nullable()->comment('Gigi 85-75');

            // ============================
            // SECTION 3: PEMERIKSAAN KLINIS
            // ============================
            $table->enum('occlusi', [
                'normal_bite',
                'cross_bite',
                'steep_bite',
            ])->default('normal_bite');

            $table->enum('torus_palatinus', [
                'tidak_ada',
                'kecil',
                'sedang',
                'besar',
                'multiple',
            ])->default('tidak_ada');

            $table->enum('torus_mandibularis', [
                'tidak_ada',
                'sisi_kiri',
                'sisi_kanan',
                'kedua_sisi',
            ])->default('tidak_ada');

            $table->enum('palatum', [
                'dalam',
                'sedang',
                'rendah',
            ])->default('sedang');

            $table->boolean('diastema_ada')->default(false);
            $table->text('diastema_keterangan')->nullable()->comment('Dimana dan lebarnya');

            $table->boolean('gigi_anomali_ada')->default(false);
            $table->text('gigi_anomali_keterangan')->nullable()->comment('Gigi mana dan bentuknya');

            $table->text('lain_lain')->nullable();

            // DMF Index
            $table->unsignedInteger('d_index')->default(0)->comment('Decay');
            $table->unsignedInteger('m_index')->default(0)->comment('Missing');
            $table->unsignedInteger('f_index')->default(0)->comment('Filling');

            // Foto & Rontgen
            $table->unsignedInteger('jumlah_foto')->default(0);
            $table->string('jenis_foto')->nullable()->comment('digital/intraoral');
            $table->unsignedInteger('jumlah_rontgen')->default(0);
            $table->string('jenis_rontgen')->nullable()->comment('Dental/PA/OPG/Caph');

            // ============================
            // SECTION 4: VERIFIKASI
            // ============================
            $table->string('diperiksa_oleh')->nullable();
            $table->date('tanggal_pemeriksaan')->nullable();

            // ============================
            // STATUS
            // ============================
            $table->enum('status', [
                'draft',
                'completed',
                'cancelled',
            ])->default('draft');

            // ============================
            // AUDIT
            // ============================
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ============================
            // FOREIGN KEYS
            // ============================
            $table->foreign('pasien_id')
                ->references('id')
                ->on('pasien')
                ->cascadeOnDelete();

            $table->foreign('order_layanan_id')
                ->references('id')
                ->on('order_layanan')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('user')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')
                ->on('user')
                ->nullOnDelete();

            // ============================
            // INDEXES
            // ============================
            $table->index(['pasien_id', 'tanggal_kunjungan']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_examinations');
    }
};