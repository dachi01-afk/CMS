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
        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->id('id_rekam_medis');
            $table->foreignId('kunjungan_id')->constrained('kunjungan', 'id_kunjungan')->cascadeOnDelete();
            $table->dateTime('waktu_resep_selesai')->nullable();
            $table->text('keluhan')->nullable();
            $table->text('prosedur_rencana')->nullable();
            $table->text('informasi_kondisi_pasien')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekam_medis');
    }
};
