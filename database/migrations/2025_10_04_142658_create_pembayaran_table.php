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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('pasien', 'id', 'pembayaran_pasien_id')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->integer('total_tagihan');
            $table->enum('status', ['Sudah Bayar', 'Belum Bayar']);
            $table->dateTime('tanggal_pembayaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
