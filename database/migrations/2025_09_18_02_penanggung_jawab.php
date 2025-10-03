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
        Schema::create('penanggung_jawab', function (Blueprint $table) {
            $table->id('id_penanggung_jawab');
            $table->foreignId('pasien_id')->constrained('pasien', 'id_pasien');
            $table->string('nama_lengkap');
            $table->enum('hubungan_dengan_pasien', ['Orang Tua', 'Pasangan', 'Anak', 'Saudara Kandung', 'Lainnya'])->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->enum('golongan_darah', ['A', 'B', 'AB', 'O'])->nullable();
            $table->string('pekerjaan')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('no_tlp')->nullable();
            $table->string('email')->nullable();
            $table->boolean('alamat_sama_dengan_pasien')->default(true);
            $table->string('alamat_rumah')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kota_kabupaten')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kode_pos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penanggung_jawab');
    }
};
