<?php

namespace Database\Seeders;

use App\Models\Poli;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PoliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $listPoli = ['Poli Gigi', 'Poli Anak', 'Poli THT'];

        foreach ($listPoli as $poli) {
            Poli::create([
                'nama_poli' => $poli,
            ]);
        }
    }
}
