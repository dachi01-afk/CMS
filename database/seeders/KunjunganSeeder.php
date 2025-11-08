<?php

namespace Database\Seeders;

use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Poli;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class KunjunganSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Pastikan minimal ada 1 POLI & 3 PASIEN (buat dummy bila kosong)
        if (Poli::count() === 0) {
            Poli::create(['nama_poli' => 'Poli Umum', 'keterangan' => 'Dummy']);
        }
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

        $poliId   = Poli::value('id');
        $pasienId = Pasien::pluck('id');

        $keluhan  = ['Sakit gigi', 'Demam tinggi', 'Batuk pilek', 'Nyeri perut', 'Susah tidur'];

        // Buat 8 kunjungan: 3 hari ke belakang, hari ini, dan 4 hari ke depan
        $tanggalList = [];
        for ($i = -3; $i <= 4; $i++) {
            $tanggalList[] = Carbon::today()->addDays($i)->toDateString();
        }

        foreach ($tanggalList as $tgl) {
            // antrian dimulai dari jumlah yang sudah ada (per tanggal+ poli)
            $existing = Kunjungan::whereDate('tanggal_kunjungan', $tgl)
                ->where('poli_id', $poliId)
                ->count();

            // bikin 1–2 kunjungan per tanggal
            $n = $faker->numberBetween(1, 2);
            for ($i = 0; $i < $n; $i++) {
                $no = str_pad($existing + $i + 1, 3, '0', STR_PAD_LEFT);

                Kunjungan::create([
                    'poli_id'           => $poliId,
                    'pasien_id'         => $pasienId->random(),
                    'tanggal_kunjungan' => $tgl,
                    'no_antrian'        => $no,
                    'keluhan_awal'      => $faker->randomElement($keluhan),
                    'status'            => 'Pending',
                ]);
            }
        }

        $this->command?->info('KunjunganSeeder: data kunjungan dibuat.');
    }
}
