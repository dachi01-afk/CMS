<?php

namespace Database\Seeders;

use App\Models\Pasien;
use App\Models\Pembayaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;

class PembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $dataPasien = Pasien::all();
        $listStatus = ['Sudah Bayar', 'Belum Bayar'];

        foreach ($dataPasien as $pasien) {
            $jumlahPembayaran = rand(1, 5);
            for ($i = 0; $i < $jumlahPembayaran; $i++) {
                $status = Arr::random($listStatus);
                Pembayaran::create([
                    'pasien_id' => $pasien->id,
                    'total_tagihan' => $status === 'Sudah Bayar'
                        ? $faker->numberBetween(50000, 1000000)
                        : 0,
                    'status' => $status,
                    'tanggal_pembayaran' => $faker->dateTimeBetween('-1 years', '-1 day'),
                ]);
            }
        }
    }
}
