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
        Schema::create('approve_diskon_penjualan_obat', function (Blueprint $table) {
            $table->id();

            // rekomendasi: pakai foreignId biar tipe kolom match dengan tabel target
            $table->foreignId('penjualan_obat_id')
                ->constrained('penjualan_obat', 'id', 'approve_diskon_penjualan_obat_penjualan_obat_id')
                ->cascadeOnDelete();

            $table->foreignId('requested_by')
                ->constrained('user', 'id', 'approve_diskon_penjualan_obat_requested_by')
                ->cascadeOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('user', 'id', 'approve_diskon_penjualan_obat_approved_by')
                ->nullOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->text('reason')->nullable();
            $table->text('rejection_note')->nullable();
            $table->json('diskon_items');
            $table->string('diskon_hash');

            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['penjualan_obat_id', 'status']);
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approve_diskon_penjualan_obat');
    }
};
