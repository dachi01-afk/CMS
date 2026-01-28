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
        Schema::create('order_radiologi_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_radiologi_id')
                ->constrained('order_radiologi', 'id', 'order_radiologi_detail_order_radiologi_id')
                ->casCadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('jenis_pemeriksaan_radiologi_id')
                ->constrained('jenis_pemeriksaan_radiologi', 'id', 'order_radiologi_detail_order_jenis_pemeriksaan_radiologi_id')
                ->casCadeOnUpdate()->cascadeOnDelete();
            $table->enum('status_pemeriksaan', ['Pending', 'Selesai']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_radiologi_detail');
    }
};
