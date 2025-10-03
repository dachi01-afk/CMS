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
        Schema::create('resep_obat', function (Blueprint $table) {
            $table->id('id_resep');
            $table->foreignId('rekam_medis_id')->constrained('rekam_medis', 'id_rekam_medis')->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('data_obat', 'id_obat')->cascadeOnDelete();
            $table->integer('jumlah_obat');
            $table->string('aturan_pakai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resep_obat');
    }
};
