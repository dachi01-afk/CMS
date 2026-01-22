<?php

namespace Database\Factories;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class OrderLabFactory extends Factory
{

    public function definition(): array
    {
        // Simulasi tanggal order sebulan terakhir
        $tanggalOrder = $this->faker->dateTimeBetween('-1 month', 'now');

        // Tanggal pemeriksaan biasanya sama atau beberapa hari setelah order
        $tanggalPemeriksaan = Carbon::parse($tanggalOrder)->addDays(rand(0, 3));

        // Mengambil satu kunjungan secara acak setiap kali factory dijalankan
        $kunjungan = Kunjungan::inRandomOrder()->first();

        return [
            // Format No Order: LAB-20231025-001
            'no_order_lab' => 'LAB-' . $tanggalPemeriksaan->format('Ymd') . '-' . $this->faker->unique()->numerify('####'),

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
