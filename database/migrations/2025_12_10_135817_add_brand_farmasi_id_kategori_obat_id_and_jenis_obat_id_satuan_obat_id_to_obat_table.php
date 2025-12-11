<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            // Tambah kolom FK
            $table->unsignedBigInteger('brand_farmasi_id')->nullable()->after('id');
            $table->unsignedBigInteger('kategori_obat_id')->nullable()->after('brand_farmasi_id');
            $table->unsignedBigInteger('jenis_obat_id')->nullable()->after('kategori_obat_id');
            $table->unsignedBigInteger('satuan_obat_id')->nullable()->after('jenis_obat_id');

            // Tambah constraint FK
            $table->foreign('brand_farmasi_id')
                ->references('id')
                ->on('brand_farmasi')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('kategori_obat_id')
                ->references('id')
                ->on('kategori_obat')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('jenis_obat_id')
                ->references('id')
                ->on('jenis_obat')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('satuan_obat_id')
                ->references('id')
                ->on('satuan_obat')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->dropForeign(['brand_farmasi_id']);
            $table->dropColumn('brand_farmasi_id');

            $table->dropForeign(['kategori_obat_id']);
            $table->dropColumn('kategori_obat_id');

            $table->dropForeign(['jenis_obat_id']);
            $table->dropColumn('jenis_obat_id');

            $table->dropForeign(['satuan_obat_id']);
            $table->dropColumn('satuan_obat_id');
        });
    }
};
