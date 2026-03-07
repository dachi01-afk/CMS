<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diskon_approval', function (Blueprint $table) {
            $table->id();

            // rekomendasi: pakai foreignId biar tipe kolom match dengan tabel target
            $table->foreignId('pembayaran_id')
                ->constrained('pembayaran', 'id', 'diskon_approval_pembayaran_id')
                ->cascadeOnDelete();

            $table->foreignId('requested_by')
                ->constrained('user', 'id', 'diskon_approval_requested_by')
                ->cascadeOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('user', 'id', 'diskon_approval_approved_by')
                ->nullOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->text('reason')->nullable();
            $table->text('rejection_note')->nullable();
            $table->json('diskon_items');
            $table->string('diskon_hash');

            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['pembayaran_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diskon_approval');
    }
};
