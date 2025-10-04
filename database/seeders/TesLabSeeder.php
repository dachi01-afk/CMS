<?php

namespace Database\Seeders;

use App\Models\Kunjungan;
use App\Models\TesLab;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;

class TesLabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $dataKunjungan = Kunjungan::all();
        $jenisTes = ['Hematologi', 'Kimia Klinik', 'Mikrobiologi', 'Urinalisis', 'Imunologi'];
        $hasilTes = ['Normal', 'Tidak Normal', 'Perlu Pemeriksaan Lanjut'];

        foreach ($dataKunjungan as $kunjungan) {
            // Setiap kunjungan memiliki 1–3 tes lab
            $jumlahTes = rand(1, 3);

            for ($i = 0; $i < $jumlahTes; $i++) {
                TesLab::create([
                    'kunjungan_id' => $kunjungan->id,
                    'jenis_tes' => json_encode($faker->randomElements($jenisTes, rand(1, 2))),
                    'hasil_tes' => Arr::random($hasilTes),
                    'tanggal_tes' => $faker->dateTimeBetween($kunjungan->tanggal_kunjungan, 'now'),
                ]);
            }
        }
    }
}
