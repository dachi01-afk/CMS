<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Poli;
use Illuminate\Database\Seeder;

class JadwalDokterSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan minimal ada POLI & DOKTER (buat dummy bila kosong)
        if (Poli::count() === 0) {
            Poli::create(['nama_poli' => 'Poli Umum', 'keterangan' => 'Dummy']);
        }
        if (Dokter::count() === 0) {
            Dokter::create([
                'nama_dokter' => 'drg. Suniaty',
                // kalau ada kolom spesialis / poli_id, sesuaikan:
                // 'poli_id'     => Poli::value('id'),
            ]);
            Dokter::create(['nama_dokter' => 'dr. Andi Pratama']);
        }

        $poliId  = Poli::value('id');
        $dokters = Dokter::get();

        $jadwal = [
            ['hari' => 'Senin',  'jam_awal' => '08:00:00', 'jam_selesai' => '12:00:00'],
            ['hari' => 'Selasa', 'jam_awal' => '08:00:00', 'jam_selesai' => '12:00:00'],
            ['hari' => 'Rabu',   'jam_awal' => '13:00:00', 'jam_selesai' => '16:00:00'],
            ['hari' => 'Kamis',  'jam_awal' => '08:00:00', 'jam_selesai' => '12:00:00'],
            ['hari' => 'Jumat',  'jam_awal' => '10:00:00', 'jam_selesai' => '12:00:00'],
            ['hari' => 'Sabtu',  'jam_awal' => '09:00:00', 'jam_selesai' => '11:30:00'],
        ];

        foreach ($dokters as $d) {
            foreach ($jadwal as $j) {
                JadwalDokter::firstOrCreate([
                    'dokter_id'   => $d->id,
                    'poli_id'     => $poliId,
                    'hari'        => $j['hari'],
                    'jam_awal'    => $j['jam_awal'],
                    'jam_selesai' => $j['jam_selesai'],
                ]);
            }
        }

        $this->command?->info('JadwalDokterSeeder: jadwal per dokter dibuat.');
    }
}
