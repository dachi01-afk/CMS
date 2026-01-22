<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_lab_detail', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_lab_id')
                ->constrained('order_lab')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('jenis_pemeriksaan_lab_id')
                ->constrained('jenis_pemeriksaan_lab')
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // atau cascadeOnDelete() kalau mau ikut terhapus juga

            $table->enum('status_pemeriksaan', ['Pending', 'Selesai'])->default('Pending');
            $table->timestamps();

            $table->unique(['order_lab_id', 'jenis_pemeriksaan_lab_id'], 'uniq_orderlab_jenis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_lab_detail');
    }
};
