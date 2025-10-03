<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PasienSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $jenisKelamin = ['Laki-laki', 'Perempuan'];
        $agama = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
        $statusKawin = ['Belum Menikah', 'Menikah', 'Cerai Hidup', 'Cerai Mati'];
        $golonganDarah = ['A', 'B', 'AB', 'O'];
        $pendidikan = ['SD', 'SMP', 'SMA', 'Diploma', 'Sarjana', 'Magister'];

        for ($i = 0; $i < 20; $i++) {
            $jenisKelaminAcak = $faker->randomElement($jenisKelamin);
            $namaLengkap = ($jenisKelaminAcak === 'Laki-laki') ? $faker->name('male') : $faker->name('female');

            DB::table('pasien')->insert([
                'pas_foto' => $faker->imageUrl(), // Biarkan null, atau gunakan $faker->imageUrl() jika Anda ingin dummy URL
                'nama_lengkap' => $namaLengkap,
                'nomor_rm' => $faker->unique()->randomNumber(8),
                'tempat_lahir' => $faker->city,
                'tanggal_lahir' => $faker->date('Y-m-d', '2000-01-01'),
                'nomor_ktp' => $faker->unique()->nik(),
                'jenis_kelamin' => $jenisKelaminAcak,
                'agama' => $faker->randomElement($agama),
                'status' => $faker->randomElement($statusKawin),
                'golongan_darah' => $faker->randomElement($golonganDarah),
                'pendidikan_terakhir' => $faker->randomElement($pendidikan),
                'pekerjaan' => $faker->jobTitle,
                'no_tlp' => $faker->phoneNumber,
                'email' => $faker->unique()->safeEmail,
                'tanggal_pendaftaran' => $faker->dateTimeThisYear(), // Tanggal pendaftaran di tahun ini
                'alamat_rumah' => $faker->address,
                'provinsi' => $faker->state,
                'kota_kabupaten' => $faker->city,
                'kecamatan' => $faker->city,
                'kelurahan' => $faker->city,
                'kode_pos' => $faker->postcode,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
