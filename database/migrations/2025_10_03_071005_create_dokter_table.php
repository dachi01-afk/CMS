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
        Schema::create('dokter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user', 'id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('nama_dokter');
            $table->enum('spesialisasi', ['Determatologi', 'Psikiatri', 'Onkologi', 'Kardiologi']);
            $table->string('email')->unique();
            $table->string('no_hp')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokter');
    }
};
