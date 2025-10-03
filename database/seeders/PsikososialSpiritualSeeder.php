<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Pasien;

class PsikososialSpiritualSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Ambil semua ID pasien yang ada
        $pasienIds = DB::table('pasien')->pluck('id_pasien');

        if ($pasienIds->isEmpty()) {
            $this->command->info('Tidak ada data pasien ditemukan. Silakan jalankan seeder pasien terlebih dahulu.');
            return;
        }

        $kondisiPsikologis = ['Baik', 'Cemas', 'Depresi Ringan', 'Stres', 'Butuh Konseling'];
        $statusMenikah = ['Belum Menikah', 'Menikah', 'Cerai'];
        $tinggalDengan = ['Orang Tua', 'Pasangan', 'Sendiri', 'Anak', 'Keluarga Lain'];
        $pekerjaan = ['Karyawan Swasta', 'PNS', 'Wirausaha', 'Pelajar', 'Ibu Rumah Tangga', 'Tidak Bekerja'];
        $kegiatanKeagamaan = ['Sholat 5 waktu', 'Ibadah rutin di gereja', 'Meditasi', 'Doa harian', 'Tidak rutin'];
        $kegiatanSpiritualDibutuhkan = ['Konseling agama', 'Dukungan moral', 'Bimbingan spiritual', 'Tidak ada'];

        for ($i = 0; $i < 20; $i++) {
            DB::table('psikososial_spiritual')->insert([
                'pasien_id' => $faker->unique()->randomElement($pasienIds),
                'kondisi_psikologis' => $faker->randomElement($kondisiPsikologis),
                'status_menikah' => $faker->randomElement($statusMenikah),
                'tinggal_dengan' => $faker->randomElement($tinggalDengan),
                'pekerjaan' => $faker->randomElement($pekerjaan),
                'kegiatan_keagamaan_rutin' => $faker->randomElement($kegiatanKeagamaan),
                'kegiatan_spiritual_dibutuhkan' => $faker->randomElement($kegiatanSpiritualDibutuhkan),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
