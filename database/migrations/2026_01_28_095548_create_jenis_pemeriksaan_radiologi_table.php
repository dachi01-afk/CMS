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
        Schema::create('jenis_pemeriksaan_radiologi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pemeriksaan')->unique();
            $table->string('nama_pemeriksaan');
            $table->decimal('harga_pemeriksaan_radiologi', 15, 2);
            $table->enum('status', ['Active', 'Non Active']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_pemeriksaan_radiologi');
    }
};
