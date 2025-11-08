<?php

namespace Database\Seeders;

use App\Models\Obat;
use App\Models\Resep;
use App\Models\ResepObat;
use Illuminate\Database\Seeder;

class ResepObatSeeder extends Seeder
{
    public function run(): void
    {
        // Minimal 2 obat dummy bila kosong
        if (Obat::count() === 0) {
            Obat::create(['nama_obat' => 'Paracetamol 500 mg', 'stok' => 100, 'harga' => 2000]);
            Obat::create(['nama_obat' => 'Amoxicillin 500 mg', 'stok' => 100, 'harga' => 3000]);
        }

        $resepIds = Resep::pluck('id');
        $obatIds  = Obat::pluck('id');

        if ($resepIds->isEmpty()) {
            $this->command?->warn('ResepObatSeeder dilewati: resep kosong.');
            return;
        }

        foreach ($resepIds as $rId) {
            foreach (collect($obatIds)->random(min(2, $obatIds->count())) as $oId) {
                ResepObat::firstOrCreate([
                    'resep_id' => $rId,
                    'obat_id'  => $oId,
                ], [
                    'jumlah'     => rand(1, 2),
                    'dosis'      => 250.00,
                    'keterangan' => '3 kali sehari',
                    'status'     => 'Belum Diambil',
                ]);
            }
        }

        $this->command?->info('ResepObatSeeder: item obat per resep dibuat.');
    }
}
