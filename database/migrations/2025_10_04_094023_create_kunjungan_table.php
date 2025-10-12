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
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokter_id')->constrained('dokter')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('pasien_id')->constrained('pasien')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('tanggal_kunjungan');
            $table->string('no_antrian', 3)->nullable();
            $table->text('keluhan_awal');
            $table->enum('status', ['Pending', 'Waiting', 'Engaged', 'Payment', 'Succeed', 'Canceled'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan');
    }
};