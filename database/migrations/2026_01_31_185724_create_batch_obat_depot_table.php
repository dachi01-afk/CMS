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
        Schema::create('batch_obat_depot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_obat_id')
                ->constrained('batch_obat', 'id', 'batch_obat_depot_batch_obat_id')
                ->casCadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('depot_id')
                ->constrained('depot', 'id', 'batch_obat_depot_depot_id')
                ->casCadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('stok_obat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_obat_depot');
    }
};
