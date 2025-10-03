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
        Schema::create('tenaga_medis', function (Blueprint $table) {
            $table->id('id_tenaga_medis');
            $table->string('foto_profile')->nullable();
            $table->string('nama_lengkap');
            $table->string('jenis_kelamin')->nullable();
            $table->string('no_tlp')->nullable();
            $table->string('email')->nullable();
            $table->string('no_ktp')->nullable();
            $table->string('lembaga_registrasi_str')->nullable();
            $table->string('nomor_registrasi_str')->nullable();
            $table->date('masa_berlaku_str')->nullable();
            $table->string('lembaga_registrasi_sip')->nullable();
            $table->string('nomor_registrasi_sip')->nullable();
            $table->date('masa_berlaku_sip')->nullable();
            $table->string('gelar_depan')->nullable();
            $table->string('gelar_belakang')->nullable();
            $table->string('job_medis')->nullable();
            $table->string('spesialis')->nullable();
            $table->string('subspesialis')->nullable();
            $table->string('kode_antrian')->nullable();
            $table->integer('estimasi_waktu_menit')->nullable();
            $table->string('tanda_tangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenaga_medis');
    }
};
