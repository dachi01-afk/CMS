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
        Schema::create('restock_obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained(
                'supplier',
                'id',
                'restock_obat_supplier_id'
            )->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('depot_id')->constrained(
                'depot',
                'id',
                'restock_obat_depot_id'
            )->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('no_faktur')->unique();
            $table->dateTime('tanggal_terima');
            $table->decimal('total_tagihan', 18, 2);
            $table->date('tanggal_jatuh_tempo');
            $table->enum('status_transaksi', ["Draft", "Final"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restock_obat');
    }
};
