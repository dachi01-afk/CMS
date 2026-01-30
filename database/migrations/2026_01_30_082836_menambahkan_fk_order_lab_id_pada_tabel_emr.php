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
        Schema::table('emr', function (Blueprint $table) {
            // Gabungkan pembuatan kolom dan constraint dalam satu perintah
            $table->foreignId('order_lab_id')
                ->nullable()
                ->after('resep_id')
                ->constrained('order_lab', 'id', 'emr_order_lab_id') // Laravel otomatis cari kolom 'id' di tabel 'order_lab'
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emr', function (Blueprint $table) {
            // Menghapus foreign key dan kolom saat rollback
            $table->dropForeign(['order_lab_id']);
            $table->dropColumn('order_lab_id');
        });
    }
};
