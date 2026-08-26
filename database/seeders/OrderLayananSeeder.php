<?php

namespace Database\Seeders;

use App\Models\OrderLayanan;
use App\Models\Pasien;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class OrderLayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $dataPasien = Pasien::all();

        for ($i = 1; $i <= 10; $i++) {
            OrderLayanan::updateOrCreate([
                'kode_transaksi' => $faker->randomNumber(8, true),
                'tanggal_order' => $faker->dateTimeThisYear(),
                'tanggal_pembayaran' => null,
                'pasien_id' => $dataPasien->random()->id,
                'metode_pembayaran_id' => null,
                'subtotal' => 0,
                'diskon_nilai' => 0,
                'potongan_pesanan' => 0,
                'total_bayar' => 0,
                'uang_yang_diterima' => null,
                'kembalian' => null,
                'status_order_layanan' => 'Belum Bayar',
                'bukti_pembayaran' => null,
            ]);
        }
    }


    // 'pasien_id' => $faker->numberBetween(1, 10),
    // 'poli_id' => $faker->numberBetween(1, 5),
    // 'dokter_id' => $faker->numberBetween(1, 5),
    // 'metode_pembayaran_id' => $faker->numberBetween(1, 3),
    // 'tanggal_order' => $faker->dateTimeThisYear(),
    // 'tanggal_pembayaran' => $faker->dateTimeThisYear(),

    // 'total_bayar' => $faker->randomFloat(2, 100000, 1000000),
    // 'created_at' => now(),
    // 'updated_at' => now(),
}
