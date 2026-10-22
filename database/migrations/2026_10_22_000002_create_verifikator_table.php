<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel detail role verifikator.
     * Tabel `user` hanya dipakai untuk login; data lengkap per role
     * disimpan di tabel tersendiri (pola sama seperti admin/auditor).
     */
    public function up(): void
    {
        if (Schema::hasTable('verifikator')) {
            return;
        }

        Schema::create('verifikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user', 'id', 'verifikator_user_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('nama_verifikator');
            $table->string('foto_verifikator')->nullable();
            $table->string('no_hp_verifikator')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikator');
    }
};
