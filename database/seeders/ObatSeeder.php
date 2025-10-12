<?php

namespace Database\Seeders;

use App\Models\Obat;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $listObat = [
            ['nama' => 'Paracetamol',     'total_harga' => 5000.00],
            ['nama' => 'Amoxicillin',     'total_harga' => 10000.00],
            ['nama' => 'Vitamin C',       'total_harga' => 20000.00],
            ['nama' => 'Ibuprofen',       'total_harga' => 10000.00],
            ['nama' => 'Cefixime Syrup',  'total_harga' => 15000.00],
        ];

        foreach ($listObat as $obat) {
            Obat::create([
                'nama_obat'   => $obat['nama'],
                'jumlah'      => $faker->numberBetween(50, 200),
                'dosis'       => $faker->randomFloat(2, 50, 1000), // contoh: 500.00 mg
                'total_harga' => number_format($obat['total_harga'], 2, '.', ''), // contoh: 5000.00
            ]);
        }
    }
}
