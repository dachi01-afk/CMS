<?php

namespace Database\Seeders;

use App\Models\Kunjungan;
use App\Models\KunjunganLayanan;
use App\Models\Layanan;
use Illuminate\Database\Seeder;

class KunjunganLayananSeeder extends Seeder
{
    public function run(): void
    {
        $kunjunganIds = Kunjungan::pluck('id');
        $layananIds   = Layanan::pluck('id');

        if ($kunjunganIds->isEmpty() || $layananIds->isEmpty()) {
            $this->command?->warn('KunjunganLayananSeeder dilewati: kunjungan/layanan kosong.');
            return;
        }

        foreach ($kunjunganIds as $kId) {
            foreach (collect($layananIds)->random(min(2, $layananIds->count())) as $lId) {
                KunjunganLayanan::firstOrCreate([
                    'kunjungan_id' => $kId,
                    'layanan_id'   => $lId,
                ], [
                    'jumlah'       => rand(1, 3),
                ]);
            }
        }

        $this->command?->info('KunjunganLayananSeeder: relasi kunjungan-layanan dibuat.');
    }
}
