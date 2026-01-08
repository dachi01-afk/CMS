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
        Schema::create('stok_transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->date('tanggal_transaksi');

            $table->enum('jenis_transaksi', ['restock', 'return']);

            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained('supplier', 'id', 'stok_transaksi_supplier_id')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->string('nomor_faktur')->nullable();

            // $table->enum('status', ['draft', 'approved', 'rejected'])
            //     ->default('draft');

            $table->text('keterangan')->nullable();

            $table->foreignId('created_by')
                ->constrained('user', 'id', 'stok_transaksi_created_by')->cascadeOnDelete()->cascadeOnDelete();

            // $table->foreignId('approved_by')
            //     ->nullable()
            //     ->constrained('users')
            //     ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_transaksi');
    }
};
