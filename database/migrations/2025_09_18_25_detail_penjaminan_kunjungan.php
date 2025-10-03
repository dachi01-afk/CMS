<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_penjaminan_kunjungan', function (Blueprint $table) {
            $table->id('id_detail_penjaminan');

            // Foreign Key untuk kunjungan_id
            $table->foreignId('kunjungan_id')->constrained('kunjungan', 'id_kunjungan')->onDelete('cascade');


            // Foreign Key untuk penjamin_id
            $table->foreignId('penjamin_id')->constrained('data_penjamin', 'id_penjamin')->onDelete('restrict');

            $table->string('nomor_kartu_asuransi')->nullable();
            $table->string('nama_pemegang_kartu', 150)->nullable();
            $table->date('tanggal_berlaku')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_penjaminan_kunjungan');
    }
};
