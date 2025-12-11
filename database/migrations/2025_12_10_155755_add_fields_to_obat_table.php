<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TAMBAH KOLOM BARU
        Schema::table('obat', function (Blueprint $table) {
            // kolom baru yang kamu mau
            $table->string('kode_obat')->nullable()->after('id');
            $table->string('kandungan_obat')->nullable()->after('nama_obat');
            $table->date('tanggal_kadaluarsa_obat')->nullable()->after('kandungan_obat');
            $table->string('nomor_batch_obat')->nullable()->after('tanggal_kadaluarsa_obat');
            $table->decimal('harga_jual_obat', 10, 2)->nullable()->after('total_harga');
            $table->decimal('harga_otc_obat', 10, 2)->nullable()->after('harga_jual_obat');
        });

        // 2. ISI KODE_OBAT UNIK UNTUK DATA LAMA (PRODUCTION SAFE)
        DB::table('obat')
            ->whereNull('kode_obat')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('obat')
                        ->where('id', $row->id)
                        ->update([
                            // format bebas, penting unik
                            'kode_obat' => 'OBT-' . str_pad($row->id, 6, '0', STR_PAD_LEFT),
                        ]);
                }
            });

        // 3. BARU TAMBAHKAN UNIQUE INDEX
        Schema::table('obat', function (Blueprint $table) {
            $table->unique('kode_obat');
        });
    }

    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            // hapus unique dulu
            $table->dropUnique(['kode_obat']);

            // lalu hapus kolom-kolom baru
            $table->dropColumn([
                'kode_obat',
                'kandungan_obat',
                'tanggal_kadaluarsa_obat',
                'nomor_batch_obat',
                'harga_jual_obat',
                'harga_otc_obat',
            ]);
        });
    }
};
