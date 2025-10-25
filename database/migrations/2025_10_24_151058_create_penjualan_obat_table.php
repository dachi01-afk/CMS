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
        Schema::create('penjualan_obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('pasien', 'id', 'penjualan_obat_pasien_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('obat_id')->constrained('obat', 'id', 'penjualan_obat_obat_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('kode_transaksi');
            $table->integer('jumlah')->default(1);
            $table->decimal('sub_total', 15, 2)->nullable();
            $table->dateTime('tanggal_transaksi')->nullable();
            $table->timestamps();


            // Optional: pastikan kombinasi pasien+obat per transaksi tidak duplikat
            $table->unique(['pasien_id', 'obat_id', 'kode_transaksi'], 'penjualan_obat_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_obat');
    }
};
