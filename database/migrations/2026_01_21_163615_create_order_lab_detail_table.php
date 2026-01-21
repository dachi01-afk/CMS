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
        Schema::create('order_lab_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_lab_id')
                ->constrained('order_lab', 'id', 'order_lab_detail_order_lab_id')
                ->casCadeOnUpdate()->nullOnDelete();
            $table->foreignId('jenis_pemeriksaan_lab_id')
                ->constrained('jenis_pemeriksaan_lab', 'id', 'order_lab_detail_order_jenis_pemeriksaan_lab_id')
                ->casCadeOnUpdate()->nullOnDelete();
            $table->enum('status_pemeriksaan', ['Pending', 'Selesai']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_lab_detail');
    }
};
