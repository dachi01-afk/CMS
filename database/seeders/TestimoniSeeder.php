<?php

namespace Database\Seeders;

use App\Models\Pasien;
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
        $foto = 'foto_dokter.jpg';
        $video = 'Testimoni_Pelayanan_Kesehatan_di_Rumah_Sakit_Ciremai.mp4';
        $dataPasien = Pasien::all();

        foreach ($dataPasien as $pasien) {
            $jumlahTestimoni = rand(1, 3);
            for ($i = 0; $i < $jumlahTestimoni; $i++) {
                Testimoni::create([
                    'pasien_id' => $pasien->id,
                    'nama_testimoni' => $pasien->nama_pasien,
                    'umur' => $faker->numberBetween(20, 100),
                    'pekerjaan' => $faker->jobTitle,
                    'isi_testimoni' => $faker->word(),
                    'foto' => $foto,
                    'link_video' => $video,
                ]);
            }
        }
    }
}
