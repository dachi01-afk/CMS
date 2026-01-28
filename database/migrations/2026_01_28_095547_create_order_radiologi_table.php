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
        Schema::create('order_radiologi', function (Blueprint $table) {
            $table->id();
            $table->string('no_order_radiologi')->unique();
            $table->foreignId('dokter_id')
                ->constrained('dokter', 'id', 'order_radiologi_dokter_id')
                ->casCadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('pasien_id')
                ->constrained('pasien', 'id', 'order_radiologi_pasien_id')
                ->casCadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('kunjungan_id')
                ->constrained('kunjungan', 'id', 'order_radiologi_kunjungan_id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->date('tanggal_order');
            $table->date('tanggal_pemeriksaan');
            $table->time('jam_pemeriksaan');
            $table->enum('status', ['Pending', 'Diproses', 'Selesai', 'Dibatalkan']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_radiologi');
    }
};
