<?php

namespace Database\Seeders;

use App\Models\Pasien;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PasienSeeder extends Seeder
{
    public function run(): void
    {
        $rolePasien = User::where('role', 'Pasien')->get();
        $faker = Faker::create('id_ID');
        $jenisKelamin = ['Laki-laki', 'Perempuan'];
        $foto = 'foto_dokter.jpg';

        for ($i = 0; $i < $rolePasien->count(); $i++) {

            // ============================
            // GENERATE NO EMR
            // Format: RM-00000001
            // ============================
            $noEmr = 'RM-' . str_pad($i + 1, 8, '0', STR_PAD_LEFT);

            Pasien::create([
                'user_id'        => $rolePasien[$i]->id,
                'no_emr'         => $noEmr,   // ← tambahkan ini
                'nama_pasien'    => $faker->name,
                'alamat'         => $faker->address,
                'tanggal_lahir'  => $faker->dateTimeBetween('-100 years', '-1 day'),
                'jenis_kelamin'  => $faker->randomElement($jenisKelamin),
                'foto_pasien'    => $foto,
            ]);
        }
    }
}
