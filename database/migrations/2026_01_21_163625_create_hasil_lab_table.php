<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_lab', function (Blueprint $table) {
            $table->id();

            // hasil lab milik 1 order_lab_detail -> kalau detail dihapus, hasil ikut hilang
            $table->foreignId('order_lab_detail_id')
                ->constrained('order_lab_detail', 'id', 'hasil_lab_order_lab_detail_id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // perawat bisa null kalau perawatnya dihapus
            $table->foreignId('perawat_id')
                ->nullable()
                ->constrained('perawat', 'id', 'hasil_lab_perawat_id')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->decimal('nilai_hasil', 15, 2);
            $table->string('nilai_rujukan');
            $table->text('keterangan')->nullable();
            $table->date('tanggal_pemeriksaan');
            $table->time('jam_pemeriksaan');
            $table->timestamps();

            $table->index(['order_lab_detail_id', 'tanggal_pemeriksaan'], 'idx_hasil_lab_detail_tgl');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_lab');
    }
};
