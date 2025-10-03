<?php

namespace Database\Seeders;

use App\Models\Apoteker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApotekerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Apoteker::create([
            'user_id' => 3, 
            'nama_apoteker' => 'Deli Kartika',
            'email_apoteker' => 'delikartika@gmail.com',
            'no_hp_apoteker' => '081245789632',
        ]);
    }
}
