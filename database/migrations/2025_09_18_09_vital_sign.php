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
        Schema::create('vital_sign', function (Blueprint $table) {
            $table->id('id_vital_sign');
            $table->foreignId('kunjungan_id')->constrained('kunjungan', 'id_kunjungan');
            $table->decimal('tinggi_badan', 5, 2)->nullable();
            $table->decimal('berat_badan', 5, 2)->nullable();
            $table->decimal('gula_darah', 5, 2)->nullable();
            $table->decimal('suhu_tubuh', 4, 2)->nullable();
            $table->integer('sistole')->nullable();
            $table->integer('diastole')->nullable();
            $table->integer('laju_pernapasan')->nullable();
            $table->decimal('lingkar_perut', 5, 2)->nullable();
            $table->integer('denyut_nadi')->nullable();
            $table->integer('oksigen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_sign');
    }
};
