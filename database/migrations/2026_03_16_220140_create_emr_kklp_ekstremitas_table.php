<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emr_kklp_ekstremitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emr_kklp_id')
                ->constrained('emr_kklp')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->enum('anggota', ['kanan_atas', 'kiri_atas', 'kanan_bawah', 'kiri_bawah']);
            $table->string('akral')->nullable();
            $table->text('gerakan')->nullable();
            $table->text('tonus')->nullable();
            $table->text('trofi')->nullable();
            $table->text('refleks_fisiologis')->nullable();
            $table->text('refleks_patologis')->nullable();
            $table->text('sensibilitas')->nullable();
            $table->text('meningeal_signs')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emr_kklp_ekstremitas');
    }
};