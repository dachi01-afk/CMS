<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Verifikator;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VerifikatorSeeder extends Seeder
{
    /**
     * Mengisi tabel detail `verifikator` untuk setiap user dengan role Verifikator.
     * Tabel `user` hanya dipakai untuk login, data lengkap ada di sini.
     */
    public function run(): void
    {
        // Seeder 1 Data User Dengan Role Verifikator.
        // Pencarian memakai email saja supaya seeder tetap idempoten.
        User::updateOrCreate(
            ['email' => 'verifikator@gmail.com'],
            [
                'username' => 'Verifikator',
                'password' => Hash::make('passwordVerifikator'),
                'role' => 'Verifikator',
            ]
        );

        $faker = Faker::create('id_ID');

        $verifikatorUsers = User::where('role', 'Verifikator')->get();

        if ($verifikatorUsers->isEmpty()) {
            $this->command?->warn('VerifikatorSeeder: tidak ada user dengan role Verifikator.');

            return;
        }

        foreach ($verifikatorUsers as $user) {
            Verifikator::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama_verifikator' => $faker->name,
                    'no_hp_verifikator' => $faker->phoneNumber(),
                ]
            );
        }

        $this->command?->info("VerifikatorSeeder: {$verifikatorUsers->count()} data verifikator dibuat.");
    }
}
