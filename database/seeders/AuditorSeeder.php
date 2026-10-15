<?php

namespace Database\Seeders;

use App\Models\Auditor;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class AuditorSeeder extends Seeder
{
    /**
     * Mengisi tabel detail `auditor` untuk setiap user dengan role Auditor.
     * Tabel `user` hanya dipakai untuk login, data lengkap ada di sini.
     */
    public function run(): void
    {
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
