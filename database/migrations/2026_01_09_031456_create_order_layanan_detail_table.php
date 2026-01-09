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
        Schema::create('order_layanan_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_layanan_id')
                ->constrained('order_layanan', 'id', 'order_layanan_detail_order_layanan_id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('layanan_id')->nullable()
                ->constrained('layanan', 'id', 'order_layanan_detail_layanan_id')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('diskon_item', 12, 2)->default(0);
            $table->decimal('total_harga_item', 12, 2);
            $table->index('order_layanan_id');
            $table->index('layanan_id');
            $table->unique(['order_layanan_id', 'layanan_id'], 'uniq_order_layanan_layanan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_layanan_detail');
    }
};
