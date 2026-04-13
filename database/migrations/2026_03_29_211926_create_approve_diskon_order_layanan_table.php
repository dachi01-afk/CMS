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
        Schema::create('approve_diskon_order_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_layanan_id')
                ->constrained('order_layanan', 'id', 'approve_diskon_order_layanan_order_layanan_id')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId('requested_by')
                ->constrained('user', 'id', 'approve_diskon_order_layanan_requested_by_user_id')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId('approved_by')->nullable()
                ->constrained('user', 'id', 'approve_diskon_order_layanan_approved_by_user_id')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->text('reason')->nullable();
            $table->text('rejection_note')->nullable();
            $table->json('diskon_items');
            $table->string('diskon_hash');
            $table->date('approved_at');

            $table->index(['order_layanan_id', 'status']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approve_diskon_order_layanan');
    }
};
