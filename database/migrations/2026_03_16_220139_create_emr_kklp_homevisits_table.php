<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emr_kklp_homevisit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emr_kklp_id')
                ->constrained('emr_kklp')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->integer('nomor_kunjungan')->nullable();
            $table->date('tanggal')->nullable();
            $table->longText('catatan')->nullable();
            $table->longText('kesimpulan')->nullable();
            $table->longText('rencana_tindak_lanjut')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emr_kklp_homevisit');
    }
};