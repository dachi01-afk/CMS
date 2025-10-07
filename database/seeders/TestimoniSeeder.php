<?php

namespace Database\Seeders;

use App\Models\Testimoni;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TestimoniSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $faker = Faker::create('id_ID');
        $foto = 'foto_profil_dokter.jpeg';
        $video = 'VID-20251007-WA0024.mp4';

        for ($i = 0; $i < 10; $i++) {
            Testimoni::create([
                'nama_testimoni' => $faker->name,
                'umur' => $faker->numberBetween(20, 100),
                'pekerjaan' => $faker->jobTitle,
                'isi_testimoni' => $faker->word(),
                'foto' => $foto,
                'link_video' => $video,
            ]);
        }
    }
}
