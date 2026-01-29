<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FIXED VERSION: Langsung buat struktur yang benar untuk radiologi
     * - foto_hasil_radiologi (untuk menyimpan file gambar hasil pemeriksaan)
     * - keterangan (interpretasi dokter radiologi)
     * - BUKAN nilai_hasil dan nilai_rujukan (itu untuk lab)
     */
    public function up(): void
    {
        Schema::create('hasil_radiologi', function (Blueprint $table) {
            $table->id();
            
            // FK ke detail order radiologi
            $table->foreignId('order_radiologi_detail_id')
                ->constrained('order_radiologi_detail', 'id', 'hasil_radiologi_order_radiologi_detail_id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            
            // ✅ PENTING: Field untuk menyimpan PATH file hasil radiologi
            // Contoh nilai: "radiologi/2024/01/29/rontgen_thorax_123.jpg"
            // Format file: JPG, PNG, DICOM, AVI (untuk USG/fluoroskopi)
            $table->string('foto_hasil_radiologi')->nullable();
            
            // Perawat/radiografer yang melakukan pemeriksaan
            $table->foreignId('perawat_id')
                ->nullable()
                ->constrained('perawat', 'id', 'hasil_radiologi_perawat_id')
                ->casCadeOnUpdate()->cascadeOnDelete();
            $table->string('hasil_foto');
            $table->text('keterangan');
            $table->date('tanggal_pemeriksaan');
            $table->time('jam_pemeriksaan');
            
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['order_radiologi_detail_id', 'tanggal_pemeriksaan'], 'idx_hasil_rad_detail_tgl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_radiologi');
    }
};