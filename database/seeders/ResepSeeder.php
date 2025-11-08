<?php

namespace Database\Seeders;

use App\Models\Kunjungan;
use App\Models\Resep;
use Illuminate\Database\Seeder;

class ResepSeeder extends Seeder
{
    public function run(): void
    {
        $kunjunganIds = Kunjungan::pluck('id');

        if ($kunjunganIds->isEmpty()) {
            $this->command?->warn('ResepSeeder dilewati: belum ada kunjungan.');
            return;
        }

        foreach ($kunjunganIds as $kId) {
            Resep::firstOrCreate(['kunjungan_id' => $kId]);
        }

        $this->command?->info('ResepSeeder: 1 resep per kunjungan dibuat.');
    }
}
