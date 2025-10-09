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
        Schema::create('emr', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kunjungan_id')->constrained('kunjungan', 'id', 'emr_kunjungan_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            // $table->foreignId('resep_id')->constrained('resep', 'id', 'emr_resep_id')
            //     ->cascadeOnDelete()->cascadeOnUpdate();
            // $table->foreignId('teslab_id')->constrained('teslab', 'id', 'emr_teslab_id')
            //     ->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('riwayat_penyakit');
            $table->text('alergi');
            $table->text('hasil_periksa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emr');
    }
};
