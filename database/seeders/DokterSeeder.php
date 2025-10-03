<?php

namespace Database\Seeders;

use App\Models\Dokter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DokterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Dokter::create([
            'user_id' => 2,
            'nama_dokter' => 'Agung Prabowo',
            'spesialisasi' => 'Determatologi',
            'email' => 'agung@gmail.com',
            'no_hp' => '084569871245',
        ]);
    }
}
