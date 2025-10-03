<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'user_id' => 1,
            'nama_admin' => 'Jackson',
            'email_admin'=> 'jackson@gmail.com',
            'no_hp' => '0812345678',
        ]);
    }
}
