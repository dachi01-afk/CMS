<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emr_kklp_family_apgar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emr_kklp_id')
                ->constrained('emr_kklp')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->unsignedTinyInteger('adaptability')->nullable();
            $table->unsignedTinyInteger('partnership')->nullable();
            $table->unsignedTinyInteger('growth')->nullable();
            $table->unsignedTinyInteger('affection')->nullable();
            $table->unsignedTinyInteger('resolve')->nullable();

            $table->integer('total_skor')->nullable();
            $table->string('interpretasi')->nullable();

            $table->timestamps();

            $table->unique('emr_kklp_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emr_kklp_family_apgar');
    }
};