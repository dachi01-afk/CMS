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
        Schema::create('return_obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')
                ->constrained('supplier', 'id', 'return_obat_supplier_id')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('depot_id')
                ->constrained('depot', 'id', 'return_obat_depot_id')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('dibuat_oleh')
                ->constrained('user', 'id', 'return_obat_dibuat_oleh_user_id')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('diupdate_oleh')->nullable()
                ->constrained('user', 'id', 'return_obat_diupdate_oleh_user_id')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->string('kode_return')->unique();
            $table->date('tanggal_return');
            $table->decimal('total_tagihan', 18, 2);
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
        Schema::dropIfExists('return_obat');
    }
};
