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
        Schema::create('psikososial_spiritual', function (Blueprint $table) {
            $table->id('id_psikososial');
            $table->foreignId('pasien_id')->constrained('pasien', 'id_pasien');
            $table->enum('kondisi_psikologis', ['Baik', 'Cemas', 'Depresi Ringan', 'Stres', 'Butuh Konseling'])->nullable();
            $table->enum('status_menikah', ['Belum Menikah', 'Menikah', 'Cerai'])->nullable();
            $table->enum('tinggal_dengan', ['Orang Tua', 'Pasangan', 'Sendiri', 'Anak', 'Keluarga Lain'])->nullable();
            $table->enum('pekerjaan', ['Karyawan Swasta', 'PNS', 'Wirausaha', 'Pelajar', 'Ibu Rumah Tangga', 'Tidak Bekerja'])->nullable();
            $table->enum('kegiatan_keagamaan_rutin', ['Sholat 5 waktu', 'Ibadah rutin di gereja', 'Meditasi', 'Doa harian', 'Tidak rutin'])->nullable();
            $table->enum('kegiatan_spiritual_dibutuhkan', ['Konseling agama', 'Dukungan moral', 'Bimbingan spiritual', 'Tidak ada'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psikososial_spiritual');
    }
};
