<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Pastikan table pembayaran ada
        if (!Schema::hasTable('pembayaran')) {
            return;
        }

        // 2) Buat table pembayaran_detail kalau belum ada
        if (!Schema::hasTable('pembayaran_detail')) {
            Schema::create('pembayaran_detail', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('pembayaran_id');

                // Field "detail" yang asalnya ada di pembayaran
                $table->decimal('total_tagihan', 10, 2)->nullable();
                $table->enum('diskon_tipe', ['persen', 'nominal'])->nullable();
                $table->decimal('diskon_nilai', 10, 2)->default(0);
                $table->decimal('total_setelah_diskon', 10, 2)->nullable();
                $table->decimal('uang_yang_diterima', 10, 2)->nullable();
                $table->decimal('kembalian', 10, 2)->nullable();

                $table->timestamps();

                $table->index('pembayaran_id');

                // FK (opsional tapi bagus)
                $table->foreign('pembayaran_id')
                    ->references('id')->on('pembayaran')
                    ->onDelete('cascade');
            });
        }

        // 3) Copy data lama: 1 pembayaran -> 1 pembayaran_detail (idempotent)
        // Supaya aman kalau migrate ke-run lagi, kita insert hanya yang belum ada detail-nya.
        DB::transaction(function () {
            // Pakai INSERT SELECT biar cepat dan minim memory
            DB::statement("
                INSERT INTO pembayaran_detail (
                    pembayaran_id,
                    total_tagihan,
                    diskon_tipe,
                    diskon_nilai,
                    total_setelah_diskon,
                    uang_yang_diterima,
                    kembalian,
                    created_at,
                    updated_at
                )
                SELECT
                    p.id,
                    p.total_tagihan,
                    p.diskon_tipe,
                    p.diskon_nilai,
                    p.total_setelah_diskon,
                    p.uang_yang_diterima,
                    p.kembalian,
                    p.created_at,
                    p.updated_at
                FROM pembayaran p
                LEFT JOIN pembayaran_detail d ON d.pembayaran_id = p.id
                WHERE d.id IS NULL
            ");
        });

        /**
         * NOTE PENTING:
         * Aku sengaja TIDAK drop kolom-kolom lama dari tabel pembayaran.
         * Karena di production itu bisa bikin code lama langsung error kalau masih akses kolom tsb.
         *
         * Setelah kamu update code supaya baca dari pembayaran_detail,
         * baru bikin migration lanjutan untuk drop kolom di pembayaran (opsional).
         */
    }

    public function down(): void
    {
        // Rollback: hapus table detail (akan hapus data detail)
        // Biasanya production jarang rollback, tapi ini paling masuk akal.
        Schema::dropIfExists('pembayaran_detail');
    }
};
