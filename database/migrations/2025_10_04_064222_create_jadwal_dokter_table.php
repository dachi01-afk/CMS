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
        Schema::create('jadwal_dokter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokter_id')->constrained('dokter', 'id', 'jadwal_dokter_dokter_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('poli_id')->constrained('poli', 'id', 'jadwal_dokter_poli_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('hari');
            $table->time('jam_awal');
            $table->time('jam_selesai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_dokter');
    }
};
