<?php

namespace Database\Seeders;

use App\Models\DokterPoli;
use App\Models\JadwalDokter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalDokterSeeder extends Seeder
{
    public function run(): void
    {
        $rows = DokterPoli::select('id', 'dokter_id', 'poli_id')->get();
        if ($rows->isEmpty()) {
            $this->command?->warn('JadwalDokterSeeder: dokter_poli kosong. Jalankan DokterPoliSeeder dulu.');
            return;
        }

        $slots = [
            ['hari' => 'Senin',  'jam_awal' => '08:00:00', 'jam_selesai' => '12:00:00'],
            ['hari' => 'Selasa', 'jam_awal' => '08:00:00', 'jam_selesai' => '12:00:00'],
            ['hari' => 'Rabu',   'jam_awal' => '13:00:00', 'jam_selesai' => '16:00:00'],
            ['hari' => 'Kamis',  'jam_awal' => '08:00:00', 'jam_selesai' => '12:00:00'],
            ['hari' => 'Jumat',  'jam_awal' => '10:00:00', 'jam_selesai' => '12:00:00'],
            ['hari' => 'Sabtu',  'jam_awal' => '09:00:00', 'jam_selesai' => '11:30:00'],
            ['hari' => 'Minggu', 'jam_awal' => '09:00:00', 'jam_selesai' => '23:30:00'],
        ];

        DB::transaction(function () use ($rows, $slots) {
            $count = 0;
            foreach ($rows as $dp) {
                foreach ($slots as $s) {
                    JadwalDokter::updateOrCreate(
                        [
                            'dokter_poli_id' => $dp->id,
                            'hari'           => $s['hari'],
                            'jam_awal'       => $s['jam_awal'],
                            'jam_selesai'    => $s['jam_selesai'],
                        ],
                        [
                            // backup fields untuk kompatibilitas query lama
                            'dokter_id' => $dp->dokter_id,
                            'poli_id'   => $dp->poli_id,
                        ]
                    );
                    $count++;
                }
            }
            $this->command?->info("JadwalDokterSeeder: {$count} slot dibuat; semua FK diambil dari tabel relasi (dokter_poli).");
        });
    }
}
