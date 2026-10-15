<?php

namespace Database\Seeders;

use App\Models\Auditor;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuditorSeeder extends Seeder
{
    /**
     * Mengisi tabel detail `auditor` untuk setiap user dengan role Auditor.
     * Tabel `user` hanya dipakai untuk login, data lengkap ada di sini.
     */
    public function run(): void
    {
        // Seeder 1 Data User Dengan Role Auditor.
        // Pencarian memakai email saja supaya seeder tetap idempoten meski
        // hash password berbeda setiap kali dijalankan.
        User::updateOrCreate(
            ['email' => 'auditor@gmail.com'],
            [
                'username' => 'Auditor',
                'password' => Hash::make('passwordAuditor'),
                'role' => 'Auditor',
            ]
        );
        
        $faker = Faker::create('id_ID');

        $auditorUsers = User::where('role', 'Auditor')->get();

        if ($auditorUsers->isEmpty()) {
            $this->command?->warn('AuditorSeeder: tidak ada user dengan role Auditor.');

            return;
        }

        foreach ($auditorUsers as $user) {
            Auditor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama_auditor' => $faker->name,
                    'no_hp_auditor' => $faker->phoneNumber(),
                ]
            );
        }

        $this->command?->info("AuditorSeeder: {$auditorUsers->count()} data auditor dibuat.");
    }
}
