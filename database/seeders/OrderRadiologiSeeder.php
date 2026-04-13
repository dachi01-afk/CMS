<?php

namespace Database\Seeders;

use App\Models\Kunjungan;
use App\Models\OrderRadiologi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OrderRadiologiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kunjunganList = Kunjungan::whereNotNull('pasien_id')
            ->whereNotNull('dokter_id')
            ->get();

        if ($kunjunganList->isEmpty()) {
            $this->command->error('Tabel kunjungan valid masih kosong! Pastikan ada kunjungan dengan pasien_id dan dokter_id.');
            return;
        }

        $statuses = ['Pending', 'Diproses', 'Selesai', 'Dibatalkan'];

        for ($i = 1; $i <= 50; $i++) {
            $kunjungan = $kunjunganList->random();

            $tanggalOrder = Carbon::instance(fake()->dateTimeBetween('-30 days', 'now'));
            $tanggalPemeriksaan = (clone $tanggalOrder)->addDays(rand(0, 3));
            $status = fake()->randomElement($statuses);

            OrderRadiologi::factory()->create([
                'kunjungan_id' => $kunjungan->id,
                'pasien_id' => $kunjungan->pasien_id,
                'dokter_id' => $kunjungan->dokter_id,
                'tanggal_order' => $tanggalOrder->toDateString(),
                'tanggal_pemeriksaan' => $tanggalPemeriksaan->toDateString(),
                'jam_pemeriksaan' => fake()->randomElement(['08:00:00', '09:00:00', '10:00:00', '13:00:00', '15:00:00']),
                'status' => $status,
                'created_at' => $tanggalOrder,
                'updated_at' => $status === 'Selesai'
                    ? (clone $tanggalOrder)->addHours(rand(1, 6))
                    : $tanggalOrder,
            ]);
        }

        $this->command->info('50 data Order Radiologi berhasil dibuat.');
    }
}