<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel detail role auditor.
     * Tabel `user` hanya dipakai untuk login; data lengkap per role
     * disimpan di tabel tersendiri (pola sama seperti admin/kasir/perawat).
     */
    public function up(): void
    {
        if (Schema::hasTable('auditor')) {
            return;
        }

        Schema::create('auditor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user', 'id', 'auditor_user_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('nama_auditor');
            $table->string('foto_auditor')->nullable();
            $table->string('no_hp_auditor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditor');
    }
};
