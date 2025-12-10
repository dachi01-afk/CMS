<?php

namespace Database\Seeders;

use App\Models\DokterPoli;
use App\Models\Perawat;
use App\Models\PerawatDokterPoli;
use Illuminate\Database\Seeder;

class PerawatDokterPoliSeeder extends Seeder
{
    public function run(): void
    {
        $dokterPoliIds = DokterPoli::pluck('id')->toArray();
        $perawatIds    = Perawat::pluck('id')->toArray();

        if (empty($dokterPoliIds) || empty($perawatIds)) {
            return;
        }

        // 1️⃣ Setiap dokter_poli punya minimal 1 perawat
        foreach ($dokterPoliIds as $dokterPoliId) {
            $perawatId = collect($perawatIds)->random();

            PerawatDokterPoli::updateOrCreate(
                [
                    'perawat_id'     => $perawatId,
                    'dokter_poli_id' => $dokterPoliId,
                ],
                []
            );
        }

        // 2️⃣ Setiap perawat punya 1–3 dokter_poli acak
        foreach ($perawatIds as $perawatId) {
            $jumlahRelasi = rand(1, min(3, count($dokterPoliIds)));

            // kalau 1 → single value, kalau >1 → Collection
            if ($jumlahRelasi === 1) {
                $randomDokterPoliIds = [collect($dokterPoliIds)->random()];
            } else {
                $randomDokterPoliIds = collect($dokterPoliIds)
                    ->random($jumlahRelasi)
                    ->toArray(); // jadi array murni berisi id
            }

            foreach ($randomDokterPoliIds as $dokterPoliId) {
                PerawatDokterPoli::updateOrCreate(
                    [
                        'perawat_id'     => $perawatId,
                        'dokter_poli_id' => $dokterPoliId,
                    ],
                    []
                );
            }
        }

        // 3️⃣ Kombinasi random tambahan (opsional)
        for ($i = 0; $i < 10; $i++) {
            PerawatDokterPoli::updateOrCreate(
                [
                    'perawat_id'     => collect($perawatIds)->random(),
                    'dokter_poli_id' => collect($dokterPoliIds)->random(),
                ],
                []
            );
        }
    }
}
