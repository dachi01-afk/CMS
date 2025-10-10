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
        Schema::create('emr', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kunjungan_id')->constrained('kunjungan', 'id', 'emr_kunjungan_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('resep_id')->constrained('resep', 'id', 'emr_resep_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('keluhan_utama')->nullable();
            $table->text('riwayat_penyakit_sekarang')->nullable();
            $table->text('riwayat_penyakit_dahulu')->nullable();
            $table->text('riwayat_keluarga')->nullable();
            $table->string('tekanan_darah', 10)->nullable();
            $table->decimal('suhu_tubuh', 4, 1)->nullable();
            $table->integer('nadi')->nullable();
            $table->integer('pernapasan')->nullable();
            $table->integer('saturasi_oksigen')->nullable();
            $table->text('diagnosis')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emr');
    }
};
