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
        Schema::create('resep_obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resep_id')->constrained('resep', 'id', 'resep_obat_resep_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('obat_id')->constrained('obat', 'id', 'resep_obat_obat_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('jumlah');
            $table->decimal('dosis', 8, 2);
            $table->enum('status', ['Belum Diambil', 'Sudah Diambil'])->default('Belum Diambil');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resep_obat');
    }
};
