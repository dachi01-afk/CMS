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
        Schema::create('depot_bhp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depot_id')->nullable()->constrained('depot', 'id', 'depot_bhp_depot_id');
            $table->foreignId('bahan_habis_pakai_id')->nullable()->constrained(
                'bahan_habis_pakai',
                'id',
                'depot_bhp_bahan_habis_pakai_id'
            );
            $table->unsignedInteger('stok')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depot_bhp');
    }
};
