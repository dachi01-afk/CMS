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
        Schema::create('tes_lab', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emr_id')->constrained('emr', 'id', 'tes_lab_emr_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->json('jenis_tes');
            $table->string('hasil_tes');
            $table->dateTime('tanggal_tes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tes_lab');
    }
};
