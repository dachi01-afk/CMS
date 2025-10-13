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
        Schema::create('kunjungan_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kunjungan_id')->constrained('kunjungan', 'id', 'kunjungan_layanan_kunjungan_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('layanan_id')->constrained('layanan', 'id', 'kunjungan_layanan_layanan_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('jumlah');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan_layanan');
    }
};
