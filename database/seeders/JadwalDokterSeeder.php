<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\JadwalDokter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Date;

class JadwalDokterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dokter = Dokter::get();
        // $jadwal = [
        //     [
        //         'hari' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
        //         'jam_awal' => '08:00:00',
        //         'jam_selesai' => '15:00:00',
        //     ],
        //     [
        //         'hari' => ['Sabtu'],
        //         'jam_awal' => '09:00:00',
        //         'jam_selesai' => '12:00:00',
        //     ],
        // ];

        // for ($i = 0; $i < $dokter->count(); $i++) {
        //     foreach ($jadwal as $j) {
        //         JadwalDokter::create([
        //             'dokter_id' => $dokter[$i]->id,
        //             'hari' => json_encode($j['hari']),
        //             'jam_awal' => $j['jam_awal'],
        //             'jam_selesai' => $j['jam_selesai'],
        //         ]);
        //     }
        // }


        JadwalDokter::create([
            'dokter_id' => 1,
            'hari' => 'Jumat',
            'jam_awal' => '08:00:00',
            'jam_selesai' => '18:00:00',
        ]);

        JadwalDokter::create([
            'dokter_id' => 1,
            'hari' => 'Sabtu',
            'jam_awal' => '09:00:00',
            'jam_selesai' => '12:00:00',
        ]);

        JadwalDokter::create([
            'dokter_id' => 1,
            'hari' => 'Minggu',
            'jam_awal' => '00:00:00',
            'jam_selesai' => '23:00:00',
        ]);
    }
}
