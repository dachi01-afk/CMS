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
        Schema::create('jenis_pemeriksaan_lab', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satuan_lab_id')
                ->constrained('satuan_lab', 'id', 'jenis_pemeriksaan_lab_satuan_lab_id')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('kode_pemeriksaan');
            $table->string('nama_pemeriksaan');
            $table->unsignedBigInteger('nilai_normal');
            $table->decimal('harga_pemeriksaan_lab', 15, 2);
            $table->enum('status', ['Active', 'Non Active']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_pemeriksaan_lab');
    }
};
