<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;



class DataLayananSeeder extends Seeder
{
    public function run(): void
    {
        $layanan = [
            ['nama_layanan' => 'Konsultasi Dokter Umum', 'deskripsi' => 'Pemeriksaan dan konsultasi awal dengan dokter umum.', 'harga' => 50000.00],
            ['nama_layanan' => 'Pemeriksaan Gula Darah', 'deskripsi' => 'Tes cepat untuk mengukur kadar gula darah.', 'harga' => 25000.00],
            ['nama_layanan' => 'Vaksinasi Flu', 'deskripsi' => 'Pemberian vaksin influenza musiman.', 'harga' => 150000.00],
            ['nama_layanan' => 'Tindakan Jahit Luka', 'deskripsi' => 'Prosedur penjahitan luka ringan hingga sedang.', 'harga' => 100000.00],
            ['nama_layanan' => 'Pemeriksaan Cek Kesehatan Umum', 'deskripsi' => 'Paket pemeriksaan dasar: tekanan darah, gula darah, berat badan, dan konsultasi.', 'harga' => 200000.00],
            ['nama_layanan' => 'Terapi Inhalasi', 'deskripsi' => 'Prosedur pemberian obat melalui nebulizer untuk masalah pernapasan.', 'harga' => 75000.00],
            ['nama_layanan' => 'Perawatan Luka Bakar', 'deskripsi' => 'Perawatan dan pembersihan luka bakar ringan.', 'harga' => 80000.00],
            ['nama_layanan' => 'Pemasangan Infus', 'deskripsi' => 'Prosedur pemasangan infus.', 'harga' => 60000.00],
            ['nama_layanan' => 'Konsultasi Gizi', 'deskripsi' => 'Konsultasi dengan ahli gizi untuk rencana diet.', 'harga' => 90000.00],
            ['nama_layanan' => 'Suntik Vitamin C', 'deskripsi' => 'Pemberian suntik vitamin C untuk daya tahan tubuh.', 'harga' => 70000.00],
            ['nama_layanan' => 'Tes Golongan Darah', 'deskripsi' => 'Pemeriksaan untuk menentukan golongan darah.', 'harga' => 30000.00],
            ['nama_layanan' => 'Eksisi Kista', 'deskripsi' => 'Prosedur bedah minor untuk pengangkatan kista.', 'harga' => 350000.00],
            ['nama_layanan' => 'Pemeriksaan Kolesterol', 'deskripsi' => 'Tes darah untuk mengukur kadar kolesterol.', 'harga' => 45000.00],
            ['nama_layanan' => 'Konsultasi Telemedicine', 'deskripsi' => 'Konsultasi medis melalui video call.', 'harga' => 40000.00],
            ['nama_layanan' => 'Cek Tekanan Darah', 'deskripsi' => 'Pemeriksaan tekanan darah rutin.', 'harga' => 15000.00],
            ['nama_layanan' => 'Pemeriksaan Urine Lengkap', 'deskripsi' => 'Analisis sampel urine untuk mendeteksi masalah kesehatan.', 'harga' => 65000.00],
            ['nama_layanan' => 'Injeksi KB', 'deskripsi' => 'Pemberian suntik KB.', 'harga' => 120000.00],
            ['nama_layanan' => 'Paket Vaksin Anak', 'deskripsi' => 'Paket vaksinasi lengkap untuk anak.', 'harga' => 500000.00],
            ['nama_layanan' => 'Sunat (Sirkumsisi)', 'deskripsi' => 'Prosedur bedah minor sunat.', 'harga' => 1000000.00],
            ['nama_layanan' => 'Pemeriksaan Alergi', 'deskripsi' => 'Tes untuk mengidentifikasi alergi tertentu.', 'harga' => 180000.00],
        ];

        foreach ($layanan as $item) {
            DB::table('data_layanan')->insert([
                'nama_layanan' => $item['nama_layanan'],
                'deskripsi' => $item['deskripsi'],
                'harga' => $item['harga'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
