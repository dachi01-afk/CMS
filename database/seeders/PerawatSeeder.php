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
        $fotoDefault = 'foto_perawat.jpg';

        // Ambil semua user dengan role = Perawat
        $perawatUsers = User::where('role', 'Perawat')->get();
        if ($perawatUsers->isEmpty()) {
            $this->command?->warn('PerawatSeeder: tidak ada user dengan role Perawat.');
            return;
        }

        // Ambil mapping dokter-poli supaya kombinasi dokter_id & poli_id selalu valid
        $dokterPoliList = DokterPoli::select('dokter_id', 'poli_id')->get();
        if ($dokterPoliList->isEmpty()) {
            $this->command?->warn('PerawatSeeder: tabel dokter_poli kosong. Jalankan DokterPoliSeeder dulu.');
            return;
        }

        foreach ($perawatUsers as $user) {
            // Pilih kombinasi dokter–poli random untuk perawat ini
            $dp = $dokterPoliList->random();

            Perawat::updateOrCreate(
                ['user_id' => $user->id], // kalau sudah ada perawat untuk user ini, di-update saja
                [
                    'nama_perawat'   => $faker->name,
                    'no_hp_perawat'  => $faker->phoneNumber(),
                    'foto_perawat'   => $fotoDefault,

                    // 🔥 SNAPSHOT relasi dokter–poli
                    'dokter_id'      => $dp->dokter_id,
                    'poli_id'        => $dp->poli_id,
                ]
            );
        }

        $this->command?->info('PerawatSeeder: data perawat dibuat dengan mapping dokter & poli yang konsisten.');
    }
}
