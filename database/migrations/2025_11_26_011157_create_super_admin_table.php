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
        Schema::create('super_admin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user', 'id', 'super_admin_user_id')
                ->casCadeOnDelete()->cascadeOnUpdate();
            $table->string('nama_super_admin')->nullable();
            $table->string('foto_super_admin')->nullable();
            $table->string('no_hp_super_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('super_admin');
    }
};
