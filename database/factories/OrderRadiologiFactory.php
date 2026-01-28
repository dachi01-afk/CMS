<?php

namespace Database\Factories;

use App\Models\Kunjungan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderRadiologi>
 */
class OrderRadiologiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Simulasi tanggal order sebulan terakhir
        $tanggalOrder = $this->faker->dateTimeBetween('-1 month', 'now');

        // Tanggal pemeriksaan biasanya sama atau beberapa hari setelah order
        $tanggalPemeriksaan = Carbon::parse($tanggalOrder)->addDays(rand(0, 3));

        // Mengambil satu kunjungan secara acak setiap kali factory dijalankan
        $kunjungan = Kunjungan::inRandomOrder()->first();

        return [
            'no_order_radiologi' => 'RAD-' . $tanggalPemeriksaan->format('Ymd') . '-' . $this->faker->unique()->numerify('####'),

            'kunjungan_id' => $kunjungan->id, // Relasi ke kunjungan
            'pasien_id'    => $kunjungan->pasien_id, // Samakan pasien dengan pasien di kunjungan
            'dokter_id'    => $kunjungan->dokter_id, // Samakan dokter dengan dokter di kunjungan

            'tanggal_order' => $tanggalOrder,
            'tanggal_pemeriksaan' => $tanggalPemeriksaan,
            'jam_pemeriksaan' => $this->faker->time('H:i:s'),

            // Kita random statusnya agar bisa mengetes filter status
            'status' => $this->faker->randomElement(['Pending', 'Diproses', 'Selesai', 'Dibatalkan']),
        ];
    }
}
