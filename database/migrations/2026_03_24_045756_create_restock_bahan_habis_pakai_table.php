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
        Schema::create('restock_bahan_habis_pakai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')
                ->constrained('supplier', 'id', 'restock_bahan_habis_pakai_supplier_id')
                ->casCadeOnUpdate()->restrictOnDelete();
            $table->foreignId('depot_id')
                ->constrained('depot', 'id', 'restock_bahan_habis_pakai_depot_id')
                ->casCadeOnUpdate()->restrictOnDelete();
            $table->foreignId('dibuat_oleh')
                ->constrained('user', 'id', 'restock_bahan_habis_pakai_dibuat_oleh_user_id')
                ->casCadeOnUpdate()->restrictOnDelete();
            $table->foreignId('dikonfirmasi_oleh')->nullable()
                ->constrained('user', 'id', 'restock_bahan_habis_pakai_dikonfirmasi_oleh_user_id')
                ->casCadeOnUpdate()->nullOnDelete();
            $table->string('no_faktur')->unique();
            $table->date('tanggal_terima')->nullable();
            $table->decimal('total_tagihan', 18, 2);
            $table->date('tanggal_jatuh_tempo');
            $table->time('dikonfirmasi_jam')->nullable();
            $table->enum('status_restock', ['Pending', 'Succeed', 'Canceled']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restock_bahan_habis_pakai');
    }
};
