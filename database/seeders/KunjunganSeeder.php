<?php

namespace Database\Seeders;

use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\JadwalDokter;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class KunjunganSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // ✅ Asumsikan POLI sudah di-seed oleh PoliSeeder
        // Kalau mau aman, boleh cek & warning:
        // if (Poli::count() === 0) {
        //     $this->command?->warn('KunjunganSeeder: Tabel poli kosong. Jalankan PoliSeeder dulu.');
        //     return;
        // }

        // Pastikan minimal ada 3 PASIEN (kalau kosong, baru kita buat dummy)
        while (Pasien::count() < 3) {
            Pasien::create([
                'nama_pasien'   => $faker->name,
                'alamat'        => $faker->address,
                'jenis_kelamin' => $faker->randomElement(['Laki-laki', 'Perempuan']),
                'tanggal_lahir' => $faker->date(),
                'no_hp'         => $faker->phoneNumber,
                'no_rm'         => strtoupper(Str::random(8)),
            ]);
        }

        $pasienIds    = Pasien::pluck('id');
        $keluhan      = ['Sakit gigi', 'Demam tinggi', 'Batuk pilek', 'Nyeri perut', 'Susah tidur'];

        // 🔗 Ambil semua jadwal_dokter sebagai sumber snapshot (dokter + poli)
        $jadwalList = JadwalDokter::select('id', 'dokter_id', 'poli_id')->get();
        if ($jadwalList->isEmpty()) {
            $this->command?->warn('KunjunganSeeder: jadwal_dokter kosong. Jalankan JadwalDokterSeeder dulu.');
            return;
        }

        // Buat 8 hari: 3 hari ke belakang, hari ini, dan 4 hari ke depan
        $tanggalList = [];
        for ($i = -3; $i <= 4; $i++) {
            $tanggalList[] = Carbon::today()->addDays($i)->toDateString();
        }

        foreach ($tanggalList as $tgl) {
            // bikin 1–2 kunjungan per tanggal
            $n = $faker->numberBetween(1, 2);

            for ($i = 0; $i < $n; $i++) {
                // pilih jadwal_dokter random → otomatis dapat dokter_id & poli_id
                $jd = $jadwalList->random();

                // hitung antrian per TANGGAL + POLI
                $existing = Kunjungan::whereDate('tanggal_kunjungan', $tgl)
                    ->where('poli_id', $jd->poli_id)
                    ->count();

                $no = str_pad($existing + 1, 3, '0', STR_PAD_LEFT);

                Kunjungan::create([
                    'jadwal_dokter_id'  => $jd->id,
                    'dokter_id'         => $jd->dokter_id, // snapshot dokter
                    'poli_id'           => $jd->poli_id,   // snapshot poli
                    'pasien_id'         => $pasienIds->random(),
                    'tanggal_kunjungan' => $tgl,
                    'no_antrian'        => $no,
                    'keluhan_awal'      => $faker->randomElement($keluhan),
                    'status'            => 'Pending',
                ]);
            }
        }

        $this->command?->info('KunjunganSeeder: data kunjungan dibuat dengan snapshot jadwal_dokter & pasien.');
    }
}
