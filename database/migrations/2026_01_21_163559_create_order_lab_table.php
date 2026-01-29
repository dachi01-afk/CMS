<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_lab', function (Blueprint $table) {
            $table->id();
            $table->string('no_order_lab')->unique();

           
            $table->foreignId('kunjungan_id')->nullable();

            // dokter boleh null kalau dokter dihapus (SET NULL)
            $table->foreignId('dokter_id')->nullable();

            // pasien juga harus nullable kalau pakai SET NULL
            $table->foreignId('pasien_id')->nullable();

            $table->date('tanggal_order');
            $table->date('tanggal_pemeriksaan');
            $table->time('jam_pemeriksaan');
            $table->enum('status', ['Pending', 'Diproses', 'Selesai', 'Dibatalkan']);
            $table->timestamps();

            // ✅ FK kunjungan
            $table->foreign('kunjungan_id', 'order_lab_kunjungan_id')
                ->references('id')->on('kunjungan')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // FK dokter
            $table->foreign('dokter_id', 'order_lab_dokter_id')
                ->references('id')->on('dokter')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // FK pasien
            $table->foreign('pasien_id', 'order_lab_pasien_id')
                ->references('id')->on('pasien')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // optional index biar query cepat
            $table->index('kunjungan_id', 'idx_order_lab_kunjungan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_lab');
    }
};
