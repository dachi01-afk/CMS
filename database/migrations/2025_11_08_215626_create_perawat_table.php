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
        Schema::create('perawat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user', 'id', 'perawat_user_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('nama_perawat');
            $table->string('foto_perawat')->nullable();;
            $table->string('no_hp_perawat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perawat');
    }
};
