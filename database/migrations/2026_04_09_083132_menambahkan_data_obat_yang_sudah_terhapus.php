<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('obat')->updateOrInsert(
                ['id' => 145],
                [
                    'kode_obat' => 'OBT-000145',
                    'brand_farmasi_id' => null,
                    'kategori_obat_id' => 2,
                    'jenis_obat_id' => null,
                    'satuan_obat_id' => 2,
                    'nama_obat' => 'Alaxan XR Tablet',
                    'kandungan_obat' => null,
                    'jumlah' => 100,
                    'dosis' => 0.00,
                    'total_harga' => 745.00,
                    'harga_jual_obat' => 969.00,
                    'harga_otc_obat' => 0.00,
                    'created_at' => '2025-10-24 01:33:34',
                    'updated_at' => '2026-03-27 17:12:44',
                ]
            );

            DB::table('obat')->updateOrInsert(
                ['id' => 270],
                [
                    'kode_obat' => 'OBT-B1QCGL7W',
                    'brand_farmasi_id' => null,
                    'kategori_obat_id' => 2,
                    'jenis_obat_id' => null,
                    'satuan_obat_id' => 2,
                    'nama_obat' => 'Fenofibrate',
                    'kandungan_obat' => null,
                    'jumlah' => 60,
                    'dosis' => 100.00,
                    'total_harga' => 7050.00,
                    'harga_jual_obat' => 7050.00,
                    'harga_otc_obat' => 7050.00,
                    'created_at' => '2025-12-29 19:11:00',
                    'updated_at' => '2026-03-18 16:37:01',
                ]
            );
        });
    }

    public function down(): void
    {
        // Sengaja dikosongkan.
        // Jangan di-delete saat rollback karena bisa bikin relasi rusak lagi.
    }
};
