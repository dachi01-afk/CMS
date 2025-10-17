<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Poli;
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
            'Sakit Gigi',
            'Demam tinggi',
            'Batuk pilek',
            'Nyeri perut',
            'Susah tidur'
        ];

        $pasien = Pasien::all();
        $dataPoli = Poli::all();

        foreach ($pasien as $p) {
            $poli = $dataPoli->random();

            // Tentukan tanggal kunjungan pertama pasien ini
            $hariPertamaBerkunjung = $faker->dateTimeBetween('-1 years', '1 years');

            $jumlahKunjungan = rand(10, 15);
            $tanggalKunjungan = clone $hariPertamaBerkunjung; // copy supaya bisa dimodifikasi

            for ($i = 0; $i < $jumlahKunjungan; $i++) {
                // Cek jumlah kunjungan yang sudah ada di tanggal itu
                $countExisting = Kunjungan::whereDate('tanggal_kunjungan', $tanggalKunjungan->format('Y-m-d'))->count();

                // Generate nomor antrian baru
                $noAntrian = str_pad($countExisting + 1, 3, '0', STR_PAD_LEFT); // contoh: 001, 002, 003

                Kunjungan::create([
                    'poli_id' => $poli->id,
                    'pasien_id' => $p->id,
                    'tanggal_kunjungan' => $tanggalKunjungan,
                    'no_antrian' => $noAntrian,
                    'keluhan_awal' => $faker->randomElement($daftarKeluhan),
                    'status' => 'Pending',
                ]);

                // Setiap kunjungan berikutnya maju 1–7 hari
                $tanggalKunjungan->modify('+' . rand(1, 7) . ' days');

                // Biar gak lewat hari ini
                if ($tanggalKunjungan > new \DateTime('now')) {
                    $tanggalKunjungan = new \DateTime('now');
                }
            }
        }

        // $dataPoli = Poli::firstOrFail();

        // Kunjungan::create([
        //     'poli_id' => $dataPoli->id,
        //     'pasien_id' => 1,
        //     'tanggal_kunjungan' => '2025-10-13',
        //     'no_antrian' => '001',
        //     'keluhan_awal' => $daftarKeluhan[0],
        //     'status' => 'Pending',
        // ]);
        // Kunjungan::create([
        //     'poli_id' => $dataPoli->id,
        //     'pasien_id' => 1,
        //     'tanggal_kunjungan' => '2025-10-14',
        //     'no_antrian' => '001',
        //     'keluhan_awal' => $daftarKeluhan[0],
        //     'status' => 'Pending',
        // ]);
        // Kunjungan::create([
        //     'poli_id' => $dataPoli->id,
        //     'pasien_id' => 1,
        //     'tanggal_kunjungan' => '2025-10-15',
        //     'no_antrian' => '001',
        //     'keluhan_awal' => $daftarKeluhan[0],
        //     'status' => 'Pending',
        // ]);
        // Kunjungan::create([
        //     'poli_id' => $dataPoli->id,
        //     'pasien_id' => 1,
        //     'tanggal_kunjungan' => '2025-10-16',
        //     'no_antrian' => '001',
        //     'keluhan_awal' => $daftarKeluhan[0],
        //     'status' => 'Pending',
        // ]);
    }
}
