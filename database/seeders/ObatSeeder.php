<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\BrandFarmasi;
use App\Models\KategoriObat;
use App\Models\JenisObat;
use App\Models\SatuanObat;

class ObatSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $brandId    = BrandFarmasi::query()->value('id');
        $kategoriId = KategoriObat::query()->value('id');
        $jenisId    = JenisObat::query()->value('id');
        $satuanId   = SatuanObat::query()->value('id');

        if (!$brandId || !$kategoriId || !$jenisId || !$satuanId) {
            $this->command->warn('⚠️ ObatSeeder dibatalkan: FK brand/kategori/jenis/satuan belum ada.');
            return;
        }

        // Obat ID 1
        DB::table('obat')->updateOrInsert(
            ['id' => 1],
            [
                'kode_obat'        => 'OBT-00001',
                'brand_farmasi_id' => $brandId,
                'kategori_obat_id' => $kategoriId,
                'jenis_obat_id'    => $jenisId,
                'satuan_obat_id'   => $satuanId,
                'nama_obat'        => 'Paracetamol',
                'kandungan_obat'   => 'Paracetamol 500mg',
                'jumlah'           => 0, // NANTI DI-UPDATE dari batch_obat_depot
                'dosis'            => 500,
                'total_harga'      => 5000.00,
                'harga_jual_obat'  => 6500.00,
                'harga_otc_obat'   => 7500.00,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]
        );

        // Obat ID 2
        DB::table('obat')->updateOrInsert(
            ['id' => 2],
            [
                'kode_obat'        => 'OBT-00002',
                'brand_farmasi_id' => $brandId,
                'kategori_obat_id' => $kategoriId,
                'jenis_obat_id'    => $jenisId,
                'satuan_obat_id'   => $satuanId,
                'nama_obat'        => 'Amoxicillin',
                'kandungan_obat'   => 'Amoxicillin 500mg',
                'jumlah'           => 0, // NANTI DI-UPDATE dari batch_obat_depot
                'dosis'            => 500,
                'total_harga'      => 10000.00,
                'harga_jual_obat'  => 13000.00,
                'harga_otc_obat'   => 15000.00,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]
        );

        $this->command->info('✅ ObatSeeder OK (Obat ID 1 & 2).');
    }
}
