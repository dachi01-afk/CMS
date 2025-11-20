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

        // Ambil maksimal 5 user dengan role = Perawat
        $perawatUsers = User::where('role', 'Perawat')->take(5)->get();

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

        // Ambil kombinasi unik per dokter, diacak dulu biar distribusi random
        $uniqueDokterPoli = $dokterPoliList->shuffle()->unique('dokter_id')->values();

        if ($uniqueDokterPoli->isEmpty()) {
            $this->command?->warn('PerawatSeeder: tidak ada dokter unik pada tabel dokter_poli.');
            return;
        }

        $jumlahPerawat = min(5, $perawatUsers->count());
        $jumlahDokterUnik = $uniqueDokterPoli->count();

        for ($i = 0; $i < $jumlahPerawat; $i++) {
            $user = $perawatUsers[$i];

            // Rotasi dokter jika jumlah perawat > jumlah dokter
            $dp = $uniqueDokterPoli[$i % $jumlahDokterUnik];

            Perawat::updateOrCreate(
                ['user_id' => $user->id], // kalau sudah ada perawat untuk user ini, di-update saja
                [
                    'nama_perawat'   => $faker->name,
                    'no_hp_perawat'  => $faker->phoneNumber(),
                    'foto_perawat'   => $fotoDefault,

                    // Relasi dokter–poli yang valid
                    'dokter_id'      => $dp->dokter_id,
                    'poli_id'        => $dp->poli_id,
                ]
            );
        }

        $this->command?->info("PerawatSeeder: {$jumlahPerawat} data perawat dibuat dan dihubungkan ke dokter yang tersedia.");
    }
}
