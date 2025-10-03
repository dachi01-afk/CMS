<?php

namespace Database\Seeders;

use App\Models\Pasien;
use DateTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PasienSeeder extends Seeder
{
    public function run(): void
    {
        $tanggalLahir = '28-01-2004';

        $resultTanggalLahir = DateTime::createFromFormat('d-m-Y', $tanggalLahir);

        Pasien::created([
            'user_id' => 4,
            'nama_pasien' => 'Ferdinan Sianturi',
            'alamat' => 'Jln. Sakit No. 1',
            'tanggal_lahir' => $resultTanggalLahir,
        ]);
    }
}
