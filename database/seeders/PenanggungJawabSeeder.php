<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PenanggungJawabSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Ambil ID dari 20 pasien pertama yang sudah ada
        $pasienIds = DB::table('pasien')->pluck('id_pasien')->take(20);

        $hubungan = ['Orang Tua', 'Pasangan', 'Anak', 'Saudara Kandung', 'Lainnya'];
        $jenisKelamin = ['Laki-laki', 'Perempuan'];
        $golonganDarah = ['A', 'B', 'AB', 'O'];

        foreach ($pasienIds as $pasienId) {
            $jenisKelaminAcak = $faker->randomElement($jenisKelamin);
            $namaLengkap = ($jenisKelaminAcak === 'Laki-laki') ? $faker->name('Laki-laki') : $faker->name('Perempuan');

            // 50% kemungkinan alamat sama dengan pasien
            $alamatSama = $faker->boolean(50);
            $alamatRumah = $alamatSama ? null : $faker->address;
            $provinsi = $alamatSama ? null : $faker->state;
            $kotaKabupaten = $alamatSama ? null : $faker->city;
            $kecamatan = $alamatSama ? null : $faker->city;
            $kelurahan = $alamatSama ? null : $faker->city;
            $kodePos = $alamatSama ? null : $faker->postcode;

            DB::table('penanggung_jawab')->insert([
                'pasien_id' => $pasienId,
                'nama_lengkap' => $namaLengkap,
                'hubungan_dengan_pasien' => $faker->randomElement($hubungan),
                'jenis_kelamin' => $jenisKelaminAcak,
                'golongan_darah' => $faker->randomElement($golonganDarah),
                'pekerjaan' => $faker->jobTitle,
                'tanggal_lahir' => $faker->date(),
                'no_tlp' => $faker->phoneNumber,
                'email' => $faker->unique()->safeEmail,
                'alamat_sama_dengan_pasien' => $alamatSama,
                'alamat_rumah' => $alamatRumah,
                'provinsi' => $provinsi,
                'kota_kabupaten' => $kotaKabupaten,
                'kecamatan' => $kecamatan,
                'kelurahan' => $kelurahan,
                'kode_pos' => $kodePos,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
