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
        Schema::create('depot_obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depot_id')->nullable()
                ->constrained('depot', 'id', 'depot_obat_depot_id')
                ->cascadeOnDelete()->cascadeOnDelete();
            $table->foreignId('obat_id')->nullable()
                ->constrained('obat', 'id', 'depot_obat_obat_id')
                ->cascadeOnDelete()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depot_obat');
    }
};
