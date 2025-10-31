<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\Poli;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class LayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataPoli = Poli::firstOrFail();

        $listLayanan = [
            ['nama_layanan' => 'Konsultasi / pemeriksaan saja',     'harga_layanan' => 50000.00],
            ['nama_layanan' => 'Scaling / pembersihan karang gigi',     'harga_layanan' => 200000.00],
            ['nama_layanan' => 'Tambal gigi',       'harga_layanan' => 350000.00],
            ['nama_layanan' => 'Pencabutan gigi sederhana',       'harga_layanan' => 150000.00],
            ['nama_layanan' => 'Perawatan saluran akar (endodontik)',  'harga_layanan' => 300000.00],
        ];

        $faker = Faker::create('id_ID');

        for ($i = 0; $i < $dataPoli->count(); $i++) {
            foreach ($listLayanan as $layanan) {
                Layanan::create([
                    'poli_id' => $dataPoli->id,
                    'nama_layanan' => $layanan['nama_layanan'],
                    'harga_layanan' => $layanan['harga_layanan'],
                ]);
            }
        }
    }
}
