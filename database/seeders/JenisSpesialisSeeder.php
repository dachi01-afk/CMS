<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JenisSpesialisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $spesialis = [
            ['nama_spesialis' => 'Kardiologi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_spesialis' => 'Dermatologi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_spesialis' => 'Neurologi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_spesialis' => 'Pediatri', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_spesialis' => 'Ortopedi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_spesialis' => 'Gastroenterologi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_spesialis' => 'Oftalmologi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_spesialis' => 'Endokrinologi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_spesialis' => 'Psikiatri', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_spesialis' => 'Radiologi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        DB::table('jenis_spesialis')->insert($spesialis);
    }
}
