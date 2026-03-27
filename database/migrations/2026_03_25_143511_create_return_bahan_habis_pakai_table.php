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
        Schema::create('return_bahan_habis_pakai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')
                ->constrained('supplier', 'id', 'return_bahan_habis_pakai_supplier_id')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('depot_id')
                ->constrained('depot', 'id', 'return_bahan_habis_pakai_depot_id')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('dibuat_oleh')
                ->constrained('user', 'id', 'return_bahan_habis_pakai_dibuat_oleh_user_id')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('diupdate_oleh')->nullable()
                ->constrained('user', 'id', 'return_bahan_habis_pakai_diupdate_oleh_user_id')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->string('kode_return')->unique();
            $table->date('tanggal_return');
            $table->text('keterangan')->nullable();
            $table->enum('status_return', ['Pending', 'Succeed', 'Canceled']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_bahan_habis_pakai');
    }
};
