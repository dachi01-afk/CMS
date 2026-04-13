<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emr_kklp_family_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emr_kklp_id')
                ->constrained('emr_kklp')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('nama')->nullable();
            $table->string('usia')->nullable();
            $table->string('status_kesehatan')->nullable();
            $table->text('skrining')->nullable();
            $table->text('edukasi_konseling')->nullable();
            $table->text('imunisasi')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emr_kklp_family_plan');
    }
};