<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_pemeriksaan_lab', function (Blueprint $table) {
            $table->id();

            // ✅ FIX: harus nullable kalau onDelete SET NULL
            $table->foreignId('satuan_lab_id')
                ->nullable()
                ->constrained('satuan_lab', 'id', 'jenis_pemeriksaan_lab_satuan_lab_id')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('kode_pemeriksaan');
            $table->string('nama_pemeriksaan');

            // catatan: kalau nilai normal bisa ada desimal / range, jangan unsignedBigInteger
            $table->unsignedBigInteger('nilai_normal');

            $table->decimal('harga_pemeriksaan_lab', 15, 2);
            $table->enum('status', ['Active', 'Non Active']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_pemeriksaan_lab');
    }
};
