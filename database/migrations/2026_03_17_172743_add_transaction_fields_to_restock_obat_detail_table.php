<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restock_obat_detail', function (Blueprint $table) {
            $table->integer('qty')->default(1)->after('batch_obat_id');
            $table->decimal('harga_beli', 15, 2)->default(0)->after('qty');
            $table->decimal('subtotal', 15, 2)->default(0)->after('harga_beli');

            $table->enum('diskon_type', ['nominal', 'persen'])
                ->nullable()
                ->after('subtotal');

            $table->decimal('diskon_value', 15, 2)
                ->default(0)
                ->after('diskon_type');

            $table->decimal('diskon_amount', 15, 2)
                ->default(0)
                ->after('diskon_value');

            $table->decimal('total_setelah_diskon', 15, 2)
                ->default(0)
                ->after('diskon_amount');
        });
    }

    public function down(): void
    {
        Schema::table('restock_obat_detail', function (Blueprint $table) {
            $table->dropColumn([
                'qty',
                'harga_beli',
                'subtotal',
                'diskon_type',
                'diskon_value',
                'diskon_amount',
                'total_setelah_diskon',
            ]);
        });
    }
};
