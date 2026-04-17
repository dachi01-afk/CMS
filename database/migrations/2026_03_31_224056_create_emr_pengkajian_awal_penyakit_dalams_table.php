<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emr_pengkajian_awal_penyakit_dalam', function (Blueprint $table) {
            $table->id();

            $table->foreignId('emr_id')
                ->constrained('emr', 'id', 'pengkajian_pd_emr_id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('dokter_id')
                ->nullable()
                ->constrained('dokter', 'id', 'pengkajian_pd_dokter_id')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('user', 'id', 'pengkajian_pd_created_by')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('user', 'id', 'pengkajian_pd_updated_by')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->unique('emr_id', 'uniq_pengkajian_pd_emr');

            $table->date('tanggal_pengkajian')->nullable();
            $table->time('jam_pengkajian')->nullable();

            $table->string('no_rm_snapshot', 50)->nullable();
            $table->string('nik_snapshot', 50)->nullable();

            $table->text('alergi')->nullable();
            $table->enum('sumber_data', ['Pasien', 'Keluarga', 'Teman', 'Lainnya'])->nullable();
            $table->string('sumber_data_lainnya')->nullable();

            $table->boolean('nyeri_ada')->nullable();
            $table->unsignedTinyInteger('skala_nyeri')->nullable();
            $table->string('karakteristik_nyeri')->nullable();
            $table->string('lokasi_nyeri')->nullable();
            $table->string('durasi_nyeri')->nullable();
            $table->string('frekuensi_nyeri')->nullable();
            $table->enum('tren_nyeri', ['crescendo', 'decrescendo'])->nullable();

            $table->text('keluhan_utama')->nullable();
            $table->longText('riwayat_penyakit_sekarang')->nullable();

            $table->boolean('riwayat_keluarga_hipertensi')->default(false);
            $table->boolean('riwayat_keluarga_kencing_manis')->default(false);
            $table->boolean('riwayat_keluarga_jantung')->default(false);
            $table->boolean('riwayat_keluarga_asthma')->default(false);
            $table->text('riwayat_penyakit_keluarga_lain')->nullable();

            $table->longText('riwayat_pekerjaan_sosial_ekonomi_psikologi_kebiasaan')->nullable();

            $table->enum('keadaan_umum', ['Baik', 'Sedang', 'Lemah', 'Jelek'])->nullable();
            $table->enum('status_gizi', ['Baik', 'Kurang', 'Buruk'])->nullable();

            $table->unsignedTinyInteger('gcs_e')->nullable();
            $table->unsignedTinyInteger('gcs_m')->nullable();
            $table->unsignedTinyInteger('gcs_v')->nullable();
            $table->boolean('tindakan_resusitasi')->nullable();

            $table->decimal('berat_badan', 5, 2)->nullable();
            $table->decimal('tinggi_badan', 5, 2)->nullable();

            $table->string('tensi_sistolik', 10)->nullable();
            $table->string('tensi_diastolik', 10)->nullable();
            $table->decimal('suhu_axila', 4, 1)->nullable();
            $table->decimal('suhu_rectal', 4, 1)->nullable();
            $table->unsignedSmallInteger('nadi')->nullable();
            $table->unsignedSmallInteger('respirasi')->nullable();
            $table->unsignedSmallInteger('saturasi_o2')->nullable();
            $table->string('saturasi_o2_dengan', 100)->nullable();

            $table->longText('pemeriksaan_kulit')->nullable();
            $table->longText('pemeriksaan_kepala_dan_leher')->nullable();
            $table->longText('pemeriksaan_telinga_hidung_mulut')->nullable();
            $table->longText('pemeriksaan_leher')->nullable();

            $table->longText('paru_inspeksi')->nullable();
            $table->longText('paru_palpasi')->nullable();
            $table->longText('paru_perkusi')->nullable();
            $table->longText('paru_auskultasi')->nullable();

            $table->longText('jantung_inspeksi')->nullable();
            $table->longText('jantung_palpasi')->nullable();
            $table->longText('jantung_perkusi')->nullable();
            $table->longText('jantung_auskultasi')->nullable();

            $table->longText('pemeriksaan_ekstremitas')->nullable();
            $table->longText('pemeriksaan_alat_kelamin_dan_rektum')->nullable();
            $table->longText('pemeriksaan_neurologis')->nullable();

            $table->longText('diagnosa_kerja')->nullable();
            $table->longText('diagnosa_diferensial')->nullable();
            $table->longText('terapi_tindakan')->nullable();
            $table->longText('rencana_kerja')->nullable();

            $table->boolean('boleh_pulang')->nullable();
            $table->date('tanggal_pulang')->nullable();
            $table->time('jam_keluar')->nullable();

            $table->boolean('kontrol_poliklinik')->nullable();
            $table->string('nama_poli_kontrol', 150)->nullable();
            $table->date('tanggal_kontrol')->nullable();

            $table->string('dirawat_di_ruang', 150)->nullable();
            $table->string('kelas_rawat', 100)->nullable();

            $table->date('tanggal_ttd_dokter')->nullable();
            $table->time('jam_ttd_dokter')->nullable();
            $table->string('nama_dokter_ttd')->nullable();

            $table->enum('status_form', ['draft', 'final'])->default('draft');

            $table->timestamps();

            $table->index(['dokter_id', 'tanggal_pengkajian'], 'idx_pengkajian_pd_dokter_tgl');
            $table->index('status_form', 'idx_pengkajian_pd_status_form');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emr_pengkajian_awal_penyakit_dalam');
    }
};