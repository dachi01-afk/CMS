<?php

namespace Database\Seeders;

use App\Models\Perawat;
use App\Models\User;
use App\Models\DokterPoli;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PerawatSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Ambil maksimal 5 user dengan role = Perawat
        $perawatUsers = User::where('role', 'Perawat')->take(5)->get();

        if ($perawatUsers->isEmpty()) {
            $this->command?->warn('PerawatSeeder: tidak ada user dengan role Perawat.');
            return;
        }

        $fotoPerawat = 'perawat/foto-profil.jpg';

        $jumlahPerawat = min(5, $perawatUsers->count());

        for ($i = 0; $i < $jumlahPerawat; $i++) {
            $user = $perawatUsers[$i];

            Perawat::updateOrCreate(
                ['user_id' => $user->id], // kalau sudah ada perawat untuk user ini, di-update saja
                [
                    'nama_perawat'   => $faker->name,
                    'no_hp_perawat'  => $faker->phoneNumber(),
                    'foto_perawat'   => $fotoPerawat,
                ]
            );
        }

        $this->command?->info("PerawatSeeder: {$jumlahPerawat} data perawat dibuat dan dihubungkan ke dokter yang tersedia.");
    }
}
