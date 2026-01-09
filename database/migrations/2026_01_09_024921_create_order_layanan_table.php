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
        Schema::create('order_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')
                ->constrained('pasien', 'id', 'order_layanan_pasien_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('poli_id')
                ->constrained('poli', 'id', 'order_layanan_poli_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('dokter_id')->nullable()
                ->constrained('dokter', 'id', 'order_layanan_dokter_id')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('jadwal_dokter_id')->nullable()
                ->constrained('jadwal_dokter', 'id', 'order_layanan_jadwal_dokter_id')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->text('keluhan_utama')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('potongan_pesanan', 12, 2)->default(0);
            $table->decimal('total_bayar', 12, 2)->default(0);
            $table->enum('status_order_layanan', ['Draf', 'Dipesan', 'Sudah Datang', 'Menunggu Pembayaran', 'Selesai', 'Dibatalkan'])->default('Draf');
            $table->timestamps();

            $table->index(['pasien_id', 'created_at']);
            $table->index(['poli_id', 'created_at']);
            $table->index(['status_order_layanan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_layanan');
    }
};
