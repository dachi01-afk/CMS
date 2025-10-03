<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Kunjungan;
use App\Models\RekamMedis;
use App\Models\Pembayaran;

class KunjunganSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $records = [];
        $now = now();

        // Asumsi ID yang sudah ada di tabel relasi
        $maxPasienId = 10; // Asumsi ada 10 pasien
        $maxTenagaMedisId = 4; // Asumsi ada 4 dokter
        $poliIds = [1, 2, 3, 4, 5]; // Asumsi ID Poli (1=Umum, 2=Gigi, 3=Anak, 4=Jantung, 5=Mata)
        $penjaminIds = [1, 2, 3, 4, 5]; // Asumsi ID Penjamin (1=Pribadi, 2=BPJS, 3+=Asuransi/Perusahaan)

        for ($i = 1; $i <= 20; $i++) {
            $pasienId = $faker->numberBetween(1, $maxPasienId);
            $tenagaMedisId = $faker->numberBetween(1, $maxTenagaMedisId);
            $poliId = $faker->randomElement($poliIds);

            // Logika Tipe Pasien
            $tipePasien = $faker->randomElement(['Non Rujuk', 'Non Rujuk', 'Rujuk']); // 2/3 Non Rujuk
            $isRujuk = ($tipePasien == 'Rujuk');

            $namaRSPerujuk = $isRujuk ? $faker->randomElement(['RSUD Bunda', 'Puskesmas Sehat', 'Klinik Medika']) : null;
            $namaDokterPerujuk = $isRujuk ? 'Dr. ' . $faker->lastName . ', Sp.' . $faker->randomElement(['A', 'PD', 'THT']) : null;

            // Logika Penjamin
            $penjaminId = $faker->randomElement([1, 1, 1, 2, 2, 3, 4, 5]); // Proporsi lebih banyak Pribadi dan BPJS

            // Logika Status dan Waktu Pemeriksaan
            $status = $faker->randomElement(['Succeed', 'Succeed', 'Confirmed', 'Pending', 'Waiting']);
            $isDone = ($status == 'Succeed' || $status == 'Engaged');

            $waktuKunjungan = Carbon::parse($faker->dateTimeBetween('-5 days', '+3 days'));
            $jamKunjungan = $waktuKunjungan->format('H:i:s');

            $waktuMulaiPemeriksaan = $isDone
                ? Carbon::parse($waktuKunjungan)->addMinutes($faker->numberBetween(5, 30))
                : null;

            $records[] = [
                'pasien_id' => $pasienId,
                'tenaga_medis_id' => $tenagaMedisId,
                'poli_id' => $poliId,
                'kode_antrian' => strtoupper($faker->randomLetter) . str_pad($i, 3, '0', STR_PAD_LEFT),
                'tipe_pasien' => $tipePasien,
                'nama_rs_perujuk' => $namaRSPerujuk,
                'nama_dokter_perujuk' => $namaDokterPerujuk,
                'penjamin_id' => $penjaminId,
                'jenis_kunjungan' => $faker->randomElement(['Rawat Jalan Poli', 'Antri Cepat', 'Kunjungan Sehat']),
                'jenis_perawatan' => 'Rawat Jalan',
                'tanggal_kunjungan' => $waktuKunjungan->toDateString(),
                'jam_kunjungan' => $jamKunjungan,
                'waktu_mulai_pemeriksaan' => $waktuMulaiPemeriksaan,
                'status' => $status,
                'slot' => $faker->randomElement(['Pagi', 'Siang', 'Sore']),
                'lama_durasi_menit' => $faker->numberBetween(15, 60),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('kunjungan')->insert($records);
    }
}
