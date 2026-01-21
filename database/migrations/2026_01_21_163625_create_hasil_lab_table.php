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
        Schema::create('hasil_lab', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_lab_detail_id')
                ->constrained('order_lab_detail', 'id', 'hasil_lab_order_lab_detail_id')
                ->casCadeOnUpdate()->nullOnDelete();
            $table->foreignId('perawat_id')
                ->constrained('perawat', 'id', 'hasil_lab_perawat_id')
                ->casCadeOnUpdate()->nullOnDelete();
            $table->decimal('nilai_hasil', 15, 2);
            $table->string('nilai_rujukan');
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
        Schema::dropIfExists('hasil_lab');
    }
};
