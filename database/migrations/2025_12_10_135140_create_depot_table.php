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
        Schema::create('depot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipe_depot_id')->nullable()->constrained('tipe_depot', 'id', 'depot_tipe_depot_id');
            $table->string('nama_depot')->nullable();
            $table->unsignedInteger('jumlah_stok_depot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depot');
    }
};
