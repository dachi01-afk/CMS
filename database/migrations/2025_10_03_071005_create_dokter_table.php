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
        Schema::create('dokter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user', 'id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('poli_id')->constrained('poli', 'id', 'dokter_poli_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('nama_dokter');
            $table->string('foto_dokter')->nullable();
            $table->text('deskripsi_dokter')->nullable();
            $table->string('pengalaman')->nullable();
            $table->foreignId('jenis_spesialis_id')->constrained('jenis_spesialis', 'id');
            $table->string('no_hp')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokter');
    }
};
