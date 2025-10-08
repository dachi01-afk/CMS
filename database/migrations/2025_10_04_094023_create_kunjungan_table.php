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
            $table->id();
            $table->foreignId('dokter_id')->constrained('dokter', 'id', 'kunjungan_dokter_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('pasien_id')->constrained('pasien', 'id', 'kunjungan_pasien_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('tanggal_kunjungan');
            $table->string('no_antrian', 3)->nullable();
            $table->text('keluhan_awal');
            $table->enum('status', ['Pending', 'Confirmed', 'Waiting', 'Engaged', 'Succeed'])->default('Pending');
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
