<?php

namespace Database\Factories;

use App\Models\Kunjungan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderLabFactory extends Factory
{
    public function definition(): array
    {
        $tanggalOrder = $this->faker->dateTimeBetween('-1 month', 'now');
        $tanggalPemeriksaan = Carbon::parse($tanggalOrder)->addDays(rand(0, 3));

        $kunjungan = Kunjungan::inRandomOrder()->first();

        return [
            'no_order_lab' => 'LAB-'.$tanggalPemeriksaan->format('Ymd').'-'.$this->faker->unique()->numerify('####'),

            'pasien_id' => $kunjungan?->pasien_id,
            'dokter_id' => $kunjungan?->dokter_id,

            'tanggal_order' => $tanggalOrder,
            'tanggal_pemeriksaan' => $tanggalPemeriksaan,
            'jam_pemeriksaan' => $this->faker->time('H:i:s'),
            'status' => $this->faker->randomElement(['Pending', 'Diproses', 'Selesai', 'Dibatalkan']),
        ];
    }
}
