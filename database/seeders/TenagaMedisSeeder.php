<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TenagaMedisSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $jobMedis = ['Dokter', 'Perawat', 'Apoteker', 'Bidan', 'Analis Kesehatan'];
        $spesialisasi = ['Penyakit Dalam', 'Anak', 'Bedah', 'Kandungan', 'Mata', 'Gigi', 'Umum'];
        $subspesialisasi = ['Kardiologi', 'Neonatologi', 'Ortopedi', 'Onkologi', 'Endokrinologi', null];
        $gelarBelakangDokter = ['Sp.PD', 'Sp.A', 'Sp.B', 'Sp.OG', 'Sp.M', 'Sp.KG', null];
        $jenisKelamin = ['Laki-laki', 'Perempuan'];

        // Inisialisasi counter untuk membuat kode antrian unik
        $counter = [
            'Dokter' => 0,
            'Perawat' => 0,
            'Apoteker' => 0,
            'Bidan' => 0,
            'Analis Kesehatan' => 0,
        ];

        for ($i = 0; $i < 20; $i++) {
            $jenisKelaminAcak = $faker->randomElement($jenisKelamin);
            $jobMedisAcak = $faker->randomElement($jobMedis);
            $namaLengkap = ($jenisKelaminAcak === 'Laki-laki') ? $faker->name('male') : $faker->name('female');

            $gelarDepanAcak = null;
            $spesialisasiAcak = null;
            $subspesialisasiAcak = null;
            $gelarBelakangAcak = null;

            // --- Logika Penentuan Gelar, Spesialisasi, dan KODE ANTRIAN ---

            // 1. Tambah counter untuk job medis saat ini
            $counter[$jobMedisAcak]++;
            $urutan = str_pad($counter[$jobMedisAcak], 2, '0', STR_PAD_LEFT);
            $kodeAntrian = ''; // Reset kode antrian

            if ($jobMedisAcak === 'Dokter') {
                $gelarDepanAcak = $faker->randomElement(['dr.', 'drg.']);
                $spesialisasiAcak = $faker->randomElement($spesialisasi);

                // Tetapkan Gelar Belakang yang relevan (misalnya Sp.KG untuk drg.)
                if ($gelarDepanAcak === 'drg.') {
                    $spesialisasiAcak = 'Gigi';
                    $gelarBelakangAcak = 'Sp.KG';
                } else {
                    $gelarBelakangAcak = $faker->randomElement($gelarBelakangDokter);
                }

                $subspesialisasiAcak = ($spesialisasiAcak !== 'Umum' && $faker->boolean(20)) ? $faker->randomElement($subspesialisasi) : null;

                // KODE ANTRIAN UNTUK DOKTER (Berdasarkan Spesialisasi)
                $prefixDokter = match ($spesialisasiAcak) {
                    'Penyakit Dalam' => 'DR-PD',
                    'Anak' => 'DR-A',
                    'Bedah' => 'DR-B',
                    'Kandungan' => 'DR-OG',
                    'Mata' => 'DR-M',
                    'Gigi' => 'DR-G',
                    'Umum' => 'DR-U',
                    default => 'DR-'
                };
                $kodeAntrian = $prefixDokter . $urutan;
            } elseif ($jobMedisAcak === 'Perawat') {
                $gelarDepanAcak = 'Ns.';
                $kodeAntrian = 'PRW-' . $urutan; // KODE ANTRIAN UNTUK PERAWAT
            } elseif ($jobMedisAcak === 'Apoteker') {
                $gelarDepanAcak = 'Apt.';
                $kodeAntrian = 'APT-' . $urutan; // KODE ANTRIAN UNTUK APOTEKER
            } elseif ($jobMedisAcak === 'Bidan') {
                $gelarDepanAcak = 'Bd.';
                $kodeAntrian = 'BDN-' . $urutan; // KODE ANTRIAN UNTUK BIDAN
            } elseif ($jobMedisAcak === 'Analis Kesehatan') {
                $kodeAntrian = 'ANK-' . $urutan; // KODE ANTRIAN UNTUK ANALIS
            }

            // --- Logika Insert Data ---

            DB::table('tenaga_medis')->insert([
                'foto_profile' => null,
                'nama_lengkap' => $namaLengkap,
                'jenis_kelamin' => $jenisKelaminAcak,
                'no_tlp' => $faker->phoneNumber,
                'email' => $faker->unique()->safeEmail,
                // Menggunakan random number yang lebih stabil untuk NIK/KTP
                'no_ktp' => '32' . $faker->unique()->numberBetween(10000000000000, 99999999999999),
                'lembaga_registrasi_str' => 'IDI',
                'nomor_registrasi_str' => $faker->unique()->uuid(),
                'masa_berlaku_str' => $faker->dateTimeBetween('+1 year', '+5 years'),
                'lembaga_registrasi_sip' => 'Dinkes',
                'nomor_registrasi_sip' => $faker->unique()->uuid(),
                'masa_berlaku_sip' => $faker->dateTimeBetween('+1 year', '+5 years'),
                'gelar_depan' => $gelarDepanAcak,
                'gelar_belakang' => $gelarBelakangAcak,
                'job_medis' => $jobMedisAcak,
                'spesialis' => $spesialisasiAcak,
                'subspesialis' => $subspesialisasiAcak,

                // FIELD KODE ANTRIAN YANG SUDAH DIPERBAIKI
                'kode_antrian' => $kodeAntrian,

                'estimasi_waktu_menit' => $faker->numberBetween(5, 30),
                'tanda_tangan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
