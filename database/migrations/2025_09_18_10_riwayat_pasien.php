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
        Schema::create('riwayat_pasien', function (Blueprint $table) {
            $table->id('id_riwayat_pasien');
            $table->foreignId('pasien_id')->constrained('pasien', 'id_pasien');
            $table->string('nama_alergi')->nullable();
            $table->text('riwayat_penyakit_pasien')->nullable();
            $table->text('riwayat_penyakit_keluarga')->nullable();
            $table->text('riwayat_penggunaan_obat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pasien');
    }
};
