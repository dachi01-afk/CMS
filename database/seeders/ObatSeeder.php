<?php

namespace Database\Seeders;

use App\Models\Obat;
use App\Models\BrandFarmasi;
use App\Models\KategoriObat;
use App\Models\JenisObat;
use App\Models\SatuanObat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class ObatSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $now   = Carbon::now();

        // Ambil ID FK langsung dari tabel relasi
        $brandIds    = BrandFarmasi::pluck('id')->values();
        $kategoriIds = KategoriObat::pluck('id')->values();
        $jenisIds    = JenisObat::pluck('id')->values();
        $satuanIds   = SatuanObat::pluck('id')->values();

        // Jika salah satu tabel FK kosong, hentikan biar gak seed data gantung
        if ($brandIds->isEmpty() || $kategoriIds->isEmpty() || $jenisIds->isEmpty() || $satuanIds->isEmpty()) {
            $this->command->warn('⚠️ Seeder Obat dibatalkan: data FK (brand/kategori/jenis/satuan) masih kosong. Jalankan seeder FK dulu.');
            return;
        }

        $listObat = [
            ['nama' => 'Paracetamol',    'total_harga' => 5000.00,  'kandungan' => 'Paracetamol 500mg'],
            ['nama' => 'Amoxicillin',    'total_harga' => 10000.00, 'kandungan' => 'Amoxicillin 500mg'],
            ['nama' => 'Vitamin C',      'total_harga' => 20000.00, 'kandungan' => 'Ascorbic Acid 500mg'],
            ['nama' => 'Ibuprofen',      'total_harga' => 10000.00, 'kandungan' => 'Ibuprofen 400mg'],
            ['nama' => 'Cefixime Syrup', 'total_harga' => 15000.00, 'kandungan' => 'Cefixime 100mg/5ml'],
        ];

        foreach ($listObat as $i => $item) {
            $jumlah = $faker->numberBetween(50, 200);
            $dosis  = $faker->randomFloat(2, 50, 1000);

            $totalHarga = (float) $item['total_harga'];

            // contoh margin untuk harga jual / OTC
            $hargaJual = round($totalHarga * $faker->randomFloat(2, 1.10, 1.40), 2);
            $hargaOtc  = round($totalHarga * $faker->randomFloat(2, 1.20, 1.60), 2);

            // kode_obat harus unik (kolom UNIQUE)
            $kodeObat = 'OBT-' . str_pad((string)($i + 1), 5, '0', STR_PAD_LEFT);

            Obat::updateOrCreate(
                ['kode_obat' => $kodeObat],
                [
                    'nama_obat'                 => $item['nama'],
                    'brand_farmasi_id'          => $brandIds->random(),
                    'kategori_obat_id'          => $kategoriIds->random(),
                    'jenis_obat_id'             => $jenisIds->random(),
                    'satuan_obat_id'            => $satuanIds->random(),

                    'kandungan_obat'            => $item['kandungan'],
                    'tanggal_kadaluarsa_obat'   => $faker->dateTimeBetween('+3 months', '+2 years')->format('Y-m-d'),
                    'nomor_batch_obat'          => strtoupper($faker->bothify('BATCH-####??')),

                    'jumlah'                    => $jumlah,
                    'dosis'                     => $dosis,
                    'total_harga'               => $totalHarga,
                    'harga_jual_obat'           => $hargaJual,
                    'harga_otc_obat'            => $hargaOtc,

                    'created_at'                => $now,
                    'updated_at'                => $now,
                ]
            );
        }
    }
}
