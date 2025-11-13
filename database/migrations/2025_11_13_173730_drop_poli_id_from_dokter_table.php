<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            if (Schema::hasColumn('dokter', 'poli_id')) {
                // Coba hapus FK dengan nama lama (kalau dulu pakai constrained('poli','id','dokter_poli_id'))
                try {
                    $table->dropForeign('dokter_poli_id');
                } catch (\Throwable $e) {
                    // abaikan kalau tidak ada / beda nama
                }

                // Terakhir: hapus kolom poli_id
                $table->dropColumn('poli_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            if (!Schema::hasColumn('dokter', 'poli_id')) {
                $table->foreignId('poli_id')
                    ->nullable()
                    ->constrained('poli', 'id', 'dokter_poli_id')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }
        });
    }
};
