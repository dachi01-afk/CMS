<?php

namespace Database\Seeders;

use App\Models\KategoriObat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory;

class KategoriObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('id_ID');

        for ($i = 0; $i < 10; $i++) {
            KategoriObat::create([
                'nama_kategori_obat' => $faker->word(),
            ]);
        }
    }
}
