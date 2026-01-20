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
        Schema::create('riwayat_penggunaan_bahan_habis_pakai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_habis_pakai_id')
                ->constrained('bahan_habis_pakai', 'id', 'riwayat_penggunaan_bahan_habis_pakai_bahan_habis_pakai_id')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('depot_id')
                ->constrained('depot', 'id', 'riwayat_penggunaan_bahan_habis_pakai_depot_id')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('user', 'id', 'riwayat_penggunaan_bahan_habis_pakai_user_id')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('jumlah_pemakaian');
            $table->date('tanggal_pemakaian');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_penggunaan_bahan_habis_pakai');
    }
};
