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
        Schema::create('kasir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user', 'id', 'kasir_user_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('nama_kasir');
            $table->string('foto_kasir')->nullable();;
            $table->string('no_hp_kasir');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kasir');
    }
};
