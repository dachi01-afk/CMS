<?php

namespace Database\Seeders;

use App\Models\Poli;
use Illuminate\Database\Seeder;

class PoliSeeder extends Seeder
{
    public function run(): void
    {
        $listPoli = [
            // Poli Dasar
            'Poli Umum',
            'Poli Gigi',
            'Poli KIA (Kesehatan Ibu & Anak)',
            'Poli Anak',
            'Poli Lansia',
            'Poli Imunisasi',

            // Poli Spesialis
            'Poli Penyakit Dalam',
            'Poli Bedah',
            'Poli Saraf',
            'Poli Jantung',
            'Poli Paru',
            'Poli Mata',
            'Poli Kulit & Kelamin',
            'Poli THT',
            'Poli Orthopedi',
            'Poli Kebidanan & Kandungan (Obgyn)',
            'Poli Psikiatri',
            'Poli Rehabilitasi Medik',
            'Poli Gizi Klinik',
            'Poli Ginjal & Hipertensi',
            'Poli Alergi & Imunologi',

            // Poli Penunjang Medik
            'Poli Laboratorium',
            'Poli Radiologi',
            'Poli Farmasi',
            'Poli Fisioterapi',
            'Poli Rehabilitasi Medik',

            // Poli Rawat Khusus
            'Poli TB-Paru',
            'Poli HIV & IMS',
            'Poli Hemodialisis',
            'Poli VCT',
            'Poli Kesehatan Kerja',
            'Poli Kesehatan Jiwa',
            'Poli Nyeri',

            // Poli Layanan Cepat / IGD
            'Poli IGD (Instalasi Gawat Darurat)',
            'Poli Rawat Luka',
            'Poli Tindakan Medis',
        ];

        foreach ($listPoli as $poli) {
            Poli::updateOrCreate([
                'nama_poli' => $poli,
            ]);
        }
    }
}
