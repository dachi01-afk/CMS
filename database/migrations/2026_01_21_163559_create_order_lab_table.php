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
        Schema::create('order_lab', function (Blueprint $table) {
            $table->id();
            $table->string('no_order_lab')->unique();
            $table->foreignId('dokter_id')
                ->constrained('dokter', 'id', 'order_lab_dokter_id')
                ->casCadeOnUpdate()->nullOnDelete();
            $table->foreignId('pasien_id')
                ->constrained('pasien', 'id', 'order_lab_pasien_id')
                ->casCadeOnUpdate()->nullOnDelete();
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
        Schema::dropIfExists('order_lab');
    }
};
