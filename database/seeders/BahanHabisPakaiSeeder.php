<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\BrandFarmasi;
use App\Models\JenisObat;
use App\Models\SatuanObat;

class BahanHabisPakaiSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $brandId  = BrandFarmasi::query()->value('id');
        $jenisId  = JenisObat::query()->value('id');
        $satuanId = SatuanObat::query()->value('id');

        if (!$brandId || !$jenisId || !$satuanId) {
            $this->command->warn('⚠️ BahanHabisPakaiSeeder dibatalkan: FK brand/jeni/satuan belum ada.');
            return;
        }

        DB::table('bahan_habis_pakai')->updateOrInsert(
            ['id' => 1],
            [
                'brand_farmasi_id' => $brandId,
                'jenis_id'         => $jenisId,
                'satuan_id'        => $satuanId,

                'kode'             => 'BHP-00001',
                'nama_barang'      => 'Kasa Steril',
                'stok_barang'      => 0,          // dihitung dari batch_bahan_habis_pakai_depot
                'dosis'            => 1.00,
                'harga_beli_satuan_bhp' => 1000.00,
                'avg_hpp_bhp'      => 0.00,
                'harga_jual_umum_bhp' => 1500.00,
                'harga_otc_bhp'    => 1800.00,
                'keterangan'       => 'BHP untuk perawatan luka',
                'created_at'       => $now,
                'updated_at'       => $now,
            ]
        );

        DB::table('bahan_habis_pakai')->updateOrInsert(
            ['id' => 2],
            [
                'brand_farmasi_id' => $brandId,
                'jenis_id'         => $jenisId,
                'satuan_id'        => $satuanId,

                'kode'             => 'BHP-00002',
                'nama_barang'      => 'Spuit 3ml',
                'stok_barang'      => 0,
                'dosis'            => 1.00,
                'harga_beli_satuan_bhp' => 2000.00,
                'avg_hpp_bhp'      => 0.00,
                'harga_jual_umum_bhp' => 3000.00,
                'harga_otc_bhp'    => 3500.00,
                'keterangan'       => 'BHP untuk injeksi',
                'created_at'       => $now,
                'updated_at'       => $now,
            ]
        );

        $this->command->info('✅ BahanHabisPakaiSeeder OK (ID 1 & 2).');
    }
}
