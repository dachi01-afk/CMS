<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DataPenjaminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $records = [];
        $now = now();

        // Data Penjamin Wajib (Pribadi/Pemerintah)
        $records[] = [
            'nama_penjamin' => 'Pribadi/Umum',
            'tipe_penjamin' => 'Pribadi',
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $records[] = [
            'nama_penjamin' => 'BPJS Kesehatan',
            'tipe_penjamin' => 'Pemerintah',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // Daftar nama Penjamin realistis
        $asuransiList = ['Allianz Life', 'Prudential Indonesia', 'AXA Mandiri', 'Manulife', 'Cigna', 'Bumi Putra', 'AIA Financial'];
        $perusahaanList = ['PT Karya Abadi Jaya', 'PT Sejahtera Sentosa', 'PT Global Indah', 'Yayasan Pendidikan Harapan', 'PT Telekomunikasi Selular', 'PT Bank Central Asia'];

        // Menambahkan data Asuransi (tipe_penjamin: Asuransi)
        foreach ($asuransiList as $name) {
            $records[] = [
                'nama_penjamin' => $name,
                'tipe_penjamin' => 'Asuransi',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Menambahkan data Perusahaan (tipe_penjamin: Perusahaan)
        foreach ($perusahaanList as $name) {
            $records[] = [
                'nama_penjamin' => $name,
                'tipe_penjamin' => 'Perusahaan',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Menambahkan sisa record hingga 20 dengan tipe Lainnya dan Perusahaan/Asuransi acak
        $count = count($records);
        while ($count < 20) {
            $tipe = $faker->randomElement(['Perusahaan', 'Asuransi', 'Lainnya']);

            if ($tipe === 'Lainnya') {
                $name = 'Dana Sosial ' . $faker->company;
            } elseif ($tipe === 'Perusahaan') {
                $name = 'CV ' . $faker->company;
            } else { // Asuransi
                $name = 'Asuransi ' . $faker->lastName . ' Group';
            }

            // Pastikan nama unik
            $isUnique = true;
            foreach ($records as $record) {
                if ($record['nama_penjamin'] === $name) {
                    $isUnique = false;
                    break;
                }
            }

            if ($isUnique) {
                $records[] = [
                    'nama_penjamin' => $name,
                    'tipe_penjamin' => $tipe,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $count++;
            }
        }


        DB::table('data_penjamin')->insert($records);
    }
}
