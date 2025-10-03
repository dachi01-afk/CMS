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
        Schema::create('data_obat', function (Blueprint $table) {
            $table->id('id_obat');
            $table->foreignId('kategori_obat_id')->constrained('kategori_obat', 'id_kategori_obat');
            $table->foreignId('satuan_obat_id')->constrained('satuan_obat', 'id_satuan_obat');
            $table->string('barcode')->nullable()->unique();
            $table->string('nama_obat');
            $table->string('nama_brand_farmasi')->nullable();
            $table->string('jenis')->nullable();
            $table->decimal('dosis')->nullable();
            $table->integer('stok')->default(0);
            $table->integer('stok_minimal')->nullable();
            $table->date('expired_date');
            $table->string('nomor_batch')->unique();
            $table->decimal('harga_beli_satuan', 10, 2);
            $table->decimal('harga_jual_umum', 10, 2);
            $table->text('kandungan')->nullable();
            $table->boolean('is_kunci_harga')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_obat');
    }
};
