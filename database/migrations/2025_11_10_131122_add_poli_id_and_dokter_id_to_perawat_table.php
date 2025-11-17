<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perawat', function (Blueprint $table) {
            // Tambah kolom baru (nullable dulu agar aman untuk existing rows)
            $table->foreignId('poli_id')
                ->nullable()
                ->after('user_id')->constrained('poli', 'id', 'perawat_poli_id')->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreignId('dokter_id')
                ->nullable()
                ->after('poli_id')->constrained('dokter', 'id', 'perawat_dokter_id')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('perawat', function (Blueprint $table) {
            // Drop FK lalu kolomnya
            $table->dropForeign('perawat_poli_id');
            $table->dropForeign('perawat_dokter_id');
            $table->dropColumn(['poli_id', 'dokter_id']);
        });
    }
};
