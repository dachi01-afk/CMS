<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * ==========================
         * UPDATE TABLE PEMBAYARAN
         * ==========================
         */
        Schema::table('pembayaran', function (Blueprint $table) {

            // pastikan kolom ini belum ada
            if (!Schema::hasColumn('pembayaran', 'emr_id')) {
                $table->foreignId('emr_id')
                    ->after('id')
                    ->nullable()
                    ->constrained('emr', 'id', 'pembayaran_emr_id')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('pembayaran', 'kode_transaksi')) {
                $table->string('kode_transaksi')->unique()->after('emr_id');
            }

            if (!Schema::hasColumn('pembayaran', 'diskon_tipe')) {
                $table->enum('diskon_tipe', ['persen', 'nominal'])
                    ->nullable()
                    ->after('total');
            }

            if (!Schema::hasColumn('pembayaran', 'diskon_nilai')) {
                $table->decimal('diskon_nilai', 15, 2)
                    ->nullable()
                    ->after('diskon_tipe');
            }

            if (!Schema::hasColumn('pembayaran', 'total_setelah_diskon')) {
                $table->decimal('total_setelah_diskon', 15, 2)
                    ->nullable()
                    ->after('diskon_nilai');
            }

            if (!Schema::hasColumn('pembayaran', 'uang_yang_diterima')) {
                $table->decimal('uang_yang_diterima', 15, 2)
                    ->nullable()
                    ->after('total_setelah_diskon');
            }

            if (!Schema::hasColumn('pembayaran', 'kembalian')) {
                $table->decimal('kembalian', 15, 2)
                    ->nullable()
                    ->after('uang_yang_diterima');
            }

            if (!Schema::hasColumn('pembayaran', 'bukti_pembayaran')) {
                $table->string('bukti_pembayaran')->nullable();
            }
        });

        /**
         * ==========================
         * UPDATE TABLE PEMBAYARAN_DETAIL
         * ==========================
         */
        Schema::table('pembayaran_detail', function (Blueprint $table) {

            if (!Schema::hasColumn('pembayaran_detail', 'pembayaran_id')) {
                $table->foreignId('pembayaran_id')
                    ->after('id')
                    ->constrained('pembayaran', 'id', 'pembayaran_detail_pembayaran_id')
                    ->cascadeOnDelete();
            }

            // item fleksibel (satu baris = satu item)
            if (!Schema::hasColumn('pembayaran_detail', 'layanan_id')) {
                $table->foreignId('layanan_id')->nullable()->constrained('layanan', 'id', 'pembayaran_detail_layanan_id')->nullOnDelete();
            }

            if (!Schema::hasColumn('pembayaran_detail', 'resep_id')) {
                $table->foreignId('resep_id')->nullable()->constrained('resep', 'id', 'pembayaran_detail_resep_id')->nullOnDelete();
            }

            if (!Schema::hasColumn('pembayaran_detail', 'hasil_lab_id')) {
                $table->foreignId('hasil_lab_id')->nullable()->constrained('hasil_lab', 'id', 'pembayaran_detail_hasil_lab_id')->nullOnDelete();
            }

            if (!Schema::hasColumn('pembayaran_detail', 'hasil_radiologi_id')) {
                $table->foreignId('hasil_radiologi_id')->nullable()->constrained('hasil_radiologi', 'id', 'pembayaran_detail_hasil_radiologi_id')->nullOnDelete();
            }

            if (!Schema::hasColumn('pembayaran_detail', 'nama_item')) {
                $table->string('nama_item');
            }

            if (!Schema::hasColumn('pembayaran_detail', 'qty')) {
                $table->integer('qty')->default(1);
            }

            if (!Schema::hasColumn('pembayaran_detail', 'harga')) {
                $table->decimal('harga', 15, 2);
            }

            if (!Schema::hasColumn('pembayaran_detail', 'subtotal')) {
                $table->decimal('subtotal', 15, 2);
            }
        });
    }

    public function down(): void
    {
        // rollback aman → hanya drop kolom tambahan
        Schema::table('pembayaran_detail', function (Blueprint $table) {
            $table->dropColumn([
                'layanan_id',
                'resep_obat',
                'hasil_lab',
                'hasil_radiologi',
                'nama_item',
                'qty',
                'harga',
                'subtotal'
            ]);
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn([
                'kode_transaksi',
                'diskon_tipe',
                'diskon_nilai',
                'total_setelah_diskon',
                'uang_yang_diterima',
                'kembalian',
                'bukti_pembayaran'
            ]);
        });
    }
};
