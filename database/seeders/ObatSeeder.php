<?php

namespace Database\Seeders;

use App\Models\Obat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');


        $listNamaObat = [
            'Paracetamol',
            'Amoxicillin',
            'Vitamin C',
            'Ibuprofen',
            'Cefixime Syrup',
        ];

        foreach ($listNamaObat as $nama) {
            Obat::create([
                'nama_obat'   => $nama,
                'jumlah'      => $faker->numberBetween(50, 200),
                'dosis'       => $faker->randomFloat(2, 50, 1000), // contoh: 500.00 mg
                'total_harga' => $faker->numberBetween(50000, 1000000),
            ]);
        }
    }
}
