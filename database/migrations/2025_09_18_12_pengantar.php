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
        Schema::create('pengantar', function (Blueprint $table) {
            $table->id('id_pengantar');
            $table->foreignId('kunjungan_id')->constrained('kunjungan', 'id_kunjungan');
            $table->string('nama_lengkap')->nullable();
            $table->enum('hubungan_dengan_pasien', ['Orang Tua', 'Pasangan', 'Anak', 'Saudara Kandung', 'Teman', 'Lainnya'])->nullable();
            $table->string('alamat')->nullable();
            $table->string('no_tlp')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengantar');
    }
};
