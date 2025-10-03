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
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->id('id_kunjungan');
            $table->foreignId('pasien_id')->constrained('pasien', 'id_pasien');
            $table->foreignId('tenaga_medis_id')->constrained('tenaga_medis', 'id_tenaga_medis');
            $table->foreignId('poli_id')->constrained('poli', 'id_poli');
            $table->string('kode_antrian')->nullable();
            $table->enum('tipe_pasien', ['Rujuk', 'Non Rujuk']);
            $table->string('nama_rs_perujuk')->nullable();
            $table->string('nama_dokter_perujuk')->nullable();
            $table->foreignId('penjamin_id')->nullable()->constrained('data_penjamin', 'id_penjamin')->nullOnDelete();
            $table->enum('jenis_kunjungan', ['Rawat Jalan Poli', 'Antri Cepat', 'Gawat Darurat', 'Kunjungan Sehat', 'Promotif Preventif'])->nullable();
            $table->enum('jenis_perawatan', ['Rawat Jalan', 'Rawat Inap', 'IGD'])->nullable();
            $table->date('tanggal_kunjungan');
            $table->time('jam_kunjungan');
            $table->dateTime('waktu_mulai_pemeriksaan')->nullable();
            $table->enum('status', ['Pending', 'Confirmed', 'Waiting', 'Engaged', 'Succeed'])->default('Pending');
            $table->string('slot')->nullable();
            $table->integer('lama_durasi_menit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan');
    }
};
