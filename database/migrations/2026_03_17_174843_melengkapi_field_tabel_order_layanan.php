<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_layanan', function (Blueprint $table) {
            if (!Schema::hasColumn('order_layanan', 'metode_pembayaran_id')) {
                $table->unsignedBigInteger('metode_pembayaran_id')->nullable()->after('pasien_id');
                $table->foreign('metode_pembayaran_id')
                    ->references('id')
                    ->on('metode_pembayaran')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('order_layanan', 'diskon_tipe')) {
                $table->enum('diskon_tipe', ['persen', 'rupiah'])->nullable()->after('subtotal');
            }

            if (!Schema::hasColumn('order_layanan', 'diskon_nilai')) {
                $table->decimal('diskon_nilai', 12, 2)->default(0)->after('diskon_tipe');
            }

            if (!Schema::hasColumn('order_layanan', 'bukti_pembayaran')) {
                $table->string('bukti_pembayaran')->nullable()->after('status_order_layanan');
            }

            if (!Schema::hasColumn('order_layanan', 'uang_yang_diterima')) {
                $table->decimal('uang_yang_diterima', 12, 2)->nullable()->after('total_bayar');
            }

            if (!Schema::hasColumn('order_layanan', 'kembalian')) {
                $table->decimal('kembalian', 12, 2)->nullable()->after('uang_yang_diterima');
            }

            if (!Schema::hasColumn('order_layanan', 'tanggal_pembayaran')) {
                $table->dateTime('tanggal_pembayaran')->nullable()->after('tanggal_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_layanan', function (Blueprint $table) {
            if (Schema::hasColumn('order_layanan', 'metode_pembayaran_id')) {
                $table->dropForeign(['metode_pembayaran_id']);
            }

            $dropColumns = array_filter([
                Schema::hasColumn('order_layanan', 'metode_pembayaran_id') ? 'metode_pembayaran_id' : null,
                Schema::hasColumn('order_layanan', 'diskon_tipe') ? 'diskon_tipe' : null,
                Schema::hasColumn('order_layanan', 'diskon_nilai') ? 'diskon_nilai' : null,
                Schema::hasColumn('order_layanan', 'bukti_pembayaran') ? 'bukti_pembayaran' : null,
                Schema::hasColumn('order_layanan', 'uang_yang_diterima') ? 'uang_yang_diterima' : null,
                Schema::hasColumn('order_layanan', 'kembalian') ? 'kembalian' : null,
                Schema::hasColumn('order_layanan', 'tanggal_pembayaran') ? 'tanggal_pembayaran' : null,
            ]);

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
