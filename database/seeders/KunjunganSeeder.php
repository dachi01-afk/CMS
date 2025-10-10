<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class KunjunganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $daftarKeluhan = [
            'Sakit kepala',
            'Demam tinggi',
            'Batuk pilek',
            'Nyeri perut',
            'Susah tidur'
        ];

        $pasien = Pasien::all();
        $dokter = Dokter::all();

        foreach ($pasien as $p) {
            // Tentukan tanggal kunjungan pertama pasien ini
            $hariPertamaBerkunjung = $faker->dateTimeBetween('-1 years', '-1 day');

            $jumlahKunjungan = rand(3, 5);
            $tanggalKunjungan = clone $hariPertamaBerkunjung; // copy supaya bisa dimodifikasi

            for ($i = 0; $i < $jumlahKunjungan; $i++) {
                // Cek jumlah kunjungan yang sudah ada di tanggal itu
                $countExisting = Kunjungan::whereDate('tanggal_kunjungan', $tanggalKunjungan->format('Y-m-d'))->count();

                // Generate nomor antrian baru
                $noAntrian = str_pad($countExisting + 1, 3, '0', STR_PAD_LEFT); // contoh: 001, 002, 003

                Kunjungan::create([
                    'dokter_id' => $dokter->random()->id,
                    'pasien_id' => $p->id,
                    'tanggal_kunjungan' => $tanggalKunjungan,
                    'no_antrian' => $noAntrian,
                    'keluhan_awal' => $faker->randomElement($daftarKeluhan),
                    'status' => 'Engaged',
                ]);

                // Setiap kunjungan berikutnya maju 1–7 hari
                $tanggalKunjungan->modify('+' . rand(1, 7) . ' days');

                // Biar gak lewat hari ini
                if ($tanggalKunjungan > new \DateTime('now')) {
                    $tanggalKunjungan = new \DateTime('now');
                }
            }
        }
    }
}
