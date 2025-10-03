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
        Schema::create('data_penjamin', function (Blueprint $table) {
            $table->id('id_penjamin');

            // Nama unik dari entitas penjamin (misal: BPJS, Asuransi Prudential)
            $table->string('nama_penjamin', 100)->unique();

            // Klasifikasi umum penjamin (ENUM)
            $table->enum('tipe_penjamin', [
                'Pribadi',
                'Asuransi',
                'Pemerintah',
                'Perusahaan',
                'Lainnya'
            ])->default('Pribadi');

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
        Schema::dropIfExists('data_penjamin');
    }
};
