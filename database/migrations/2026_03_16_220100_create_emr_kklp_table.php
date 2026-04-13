<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emr_kklp', function (Blueprint $table) {
            $table->id();

            $table->foreignId('emr_id')
                ->constrained('emr')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('kunjungan_id')
                ->constrained('kunjungan')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('pasien_id')->nullable()
                ->constrained('pasien')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('dokter_id')->nullable()
                ->constrained('dokter')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('poli_id')->nullable()
                ->constrained('poli')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // Identitas form
            $table->string('nama_dokter_form')->nullable();
            $table->string('nim_dokter')->nullable();
            $table->string('kasus_ke')->nullable();
            $table->date('tanggal_kasus')->nullable();

            // A. Identitas pasien tambahan
            $table->string('no_kasus')->nullable();
            $table->string('telepon_pasien')->nullable();
            $table->string('agama_pasien')->nullable();
            $table->string('pendidikan_terakhir_pasien')->nullable();
            $table->string('suku_bangsa_pasien')->nullable();
            $table->date('tanggal_pemeriksaan')->nullable();
            $table->date('tanggal_homevisit')->nullable();

            // B. Anamnesis disease
            $table->longText('riwayat_penyakit_sekarang')->nullable();
            $table->longText('riwayat_penyakit_dahulu_detail')->nullable();
            $table->longText('riwayat_penyakit_keluarga_detail')->nullable();
            $table->longText('riwayat_personal_sosial')->nullable();
            $table->longText('review_sistem')->nullable();

            // C. Illness
            $table->longText('illness_pikiran')->nullable();
            $table->longText('illness_perasaan')->nullable();
            $table->longText('illness_efek_fungsi')->nullable();
            $table->longText('illness_harapan')->nullable();
            $table->longText('illness_kesimpulan')->nullable();

            // D. Family assessment naratif
            $table->longText('genogram_keterangan')->nullable();
            $table->longText('bentuk_keluarga')->nullable();
            $table->longText('siklus_kehidupan_keluarga')->nullable();
            $table->longText('family_map_keterangan')->nullable();
            $table->integer('apgar_score_total')->nullable();
            $table->string('apgar_kesimpulan')->nullable();
            $table->longText('family_life_line_ringkasan')->nullable();

            // E. Pemeriksaan fisik
            $table->string('keadaan_umum')->nullable();
            $table->string('kesadaran')->nullable();

            $table->string('lingkar_pinggang')->nullable();
            $table->string('lingkar_panggul')->nullable();
            $table->string('lingkar_lengan_atas')->nullable();
            $table->string('status_gizi')->nullable();
            $table->string('waist_hip_ratio')->nullable();

            $table->text('pemeriksaan_kulit')->nullable();
            $table->text('pemeriksaan_kelenjar_limfe')->nullable();
            $table->text('pemeriksaan_otot')->nullable();
            $table->text('pemeriksaan_tulang')->nullable();
            $table->text('pemeriksaan_sendi')->nullable();

            $table->text('pemeriksaan_kepala')->nullable();
            $table->text('pemeriksaan_mata')->nullable();
            $table->text('pemeriksaan_hidung')->nullable();
            $table->text('pemeriksaan_telinga')->nullable();
            $table->text('pemeriksaan_mulut_gigi')->nullable();
            $table->text('pemeriksaan_tenggorokan')->nullable();
            $table->text('pemeriksaan_leher')->nullable();

            $table->text('thorax_paru_inspeksi')->nullable();
            $table->text('thorax_paru_palpasi')->nullable();
            $table->text('thorax_paru_perkusi')->nullable();
            $table->text('thorax_paru_auskultasi')->nullable();

            $table->text('thorax_jantung_inspeksi')->nullable();
            $table->text('thorax_jantung_palpasi')->nullable();
            $table->text('thorax_jantung_perkusi')->nullable();
            $table->text('thorax_jantung_auskultasi')->nullable();

            $table->text('abdomen_inspeksi')->nullable();
            $table->text('abdomen_palpasi')->nullable();
            $table->text('abdomen_perkusi')->nullable();
            $table->text('abdomen_auskultasi')->nullable();

            $table->text('anogenital')->nullable();
            $table->longText('tambahan_pemeriksaan_khusus')->nullable();

            // F,G,H,I,J
            $table->longText('ringkasan_laboratorium')->nullable();
            $table->longText('ringkasan_radiologi')->nullable();
            $table->longText('ringkasan_penunjang_lain')->nullable();

            $table->longText('patogenesis_patofisiologi')->nullable();
            $table->longText('diagnosis_klinis_banding')->nullable();
            $table->longText('diagnosis_holistik')->nullable();
            $table->longText('uraian_diagnosis_holistik')->nullable();

            $table->longText('upaya_promotif')->nullable();
            $table->longText('upaya_preventif')->nullable();
            $table->longText('upaya_kuratif')->nullable();
            $table->longText('upaya_rehabilitatif')->nullable();
            $table->longText('upaya_paliatif')->nullable();

            $table->longText('copc_plan_ringkasan')->nullable();
            $table->text('kesimpulan_phbs')->nullable();

            // M. Rumah dan lingkungan
            $table->longText('kondisi_rumah')->nullable();
            $table->longText('lingkungan_sekitar_rumah')->nullable();

            // O. Catatan kunjungan rumah
            $table->longText('catatan_tambahan_homevisit')->nullable();

            // P. Evaluasi pembimbing
            $table->integer('nilai_humanisme')->nullable();
            $table->integer('nilai_komunikasi')->nullable();
            $table->integer('nilai_pemeriksaan_fisik')->nullable();
            $table->integer('nilai_penalaran_klinis')->nullable();
            $table->integer('nilai_diagnosis_holistik')->nullable();
            $table->integer('nilai_pengelolaan_komprehensif')->nullable();
            $table->integer('nilai_edukasi_konseling')->nullable();
            $table->integer('nilai_organisasi_efisiensi')->nullable();
            $table->integer('nilai_kompetensi_keseluruhan')->nullable();

            $table->integer('skor_total')->nullable();
            $table->decimal('skor_akhir', 5, 2)->nullable();

            $table->longText('komentar_pembimbing')->nullable();
            $table->longText('komentar_dokter_residen')->nullable();

            $table->enum('status_form', ['draft', 'final'])->default('draft');

            $table->timestamps();

            $table->unique('emr_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emr_kklp');
    }
};