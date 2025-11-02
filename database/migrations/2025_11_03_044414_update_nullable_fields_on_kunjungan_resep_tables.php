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
        // =============================
        // 🔹 1. Update tabel kunjungan
        // =============================
        Schema::table('kunjungan', function (Blueprint $table) {
            $table->text('keluhan_awal')->nullable()->change();
        });

        // =============================
        // 🔹 2. Update tabel resep
        // =============================
        Schema::table('resep', function (Blueprint $table) {
            // jadikan foreign nullable
            $table->foreignId('kunjungan_id')
                ->nullable()
                ->constrained('kunjungan', 'id', 'resep_kunjungan_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->change();
        });

        // =============================
        // 🔹 3. Update tabel resep_obat
        // =============================
        Schema::table('resep_obat', function (Blueprint $table) {
            $table->decimal('dosis', 8, 2)->nullable()->change();
            $table->string('keterangan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback perubahan
        Schema::table('kunjungan', function (Blueprint $table) {
            $table->text('keluhan_awal')->nullable(false)->change();
        });

        Schema::table('resep', function (Blueprint $table) {
            if (Schema::hasColumn('resep', 'kunjungan_id')) {
                // Lepas constraint lama
                $table->dropForeign(['kunjungan_id']);

                // Ubah jadi nullable
                $table->unsignedBigInteger('kunjungan_id')->nullable()->change();

                // Tambahkan foreign key baru
                $table->foreign('kunjungan_id', 'resep_kunjungan_id')
                    ->references('id')
                    ->on('kunjungan')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        });

        Schema::table('resep_obat', function (Blueprint $table) {
            $table->decimal('dosis', 8, 2)->nullable(false)->change();
            $table->string('keterangan')->nullable(false)->change();
        });
    }
};
