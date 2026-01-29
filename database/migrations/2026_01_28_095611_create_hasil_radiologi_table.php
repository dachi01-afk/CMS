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
        Schema::create('hasil_radiologi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_radiologi_detail_id')
                ->constrained('order_radiologi_detail', 'id', 'hasil_radiologi_order_radiologi_detail_id')
                ->casCadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('perawat_id')
                ->constrained('perawat', 'id', 'hasil_radiologi_perawat_id')
                ->casCadeOnUpdate()->cascadeOnDelete();
            $table->string('hasil_foto');
            $table->text('keterangan');
            $table->date('tanggal_pemeriksaan');
            $table->time('jam_pemeriksaan');
            $table->timestamps();
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
