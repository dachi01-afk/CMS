<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emr_kklp_family_screem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emr_kklp_id')
                ->constrained('emr_kklp')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->text('social_sumber_daya')->nullable();
            $table->text('social_patologis')->nullable();

            $table->text('cultural_sumber_daya')->nullable();
            $table->text('cultural_patologis')->nullable();

            $table->text('religious_sumber_daya')->nullable();
            $table->text('religious_patologis')->nullable();

            $table->text('educational_sumber_daya')->nullable();
            $table->text('educational_patologis')->nullable();

            $table->text('economic_sumber_daya')->nullable();
            $table->text('economic_patologis')->nullable();

            $table->text('medical_sumber_daya')->nullable();
            $table->text('medical_patologis')->nullable();

            $table->timestamps();

            $table->unique('emr_kklp_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emr_kklp_family_screem');
    }
};