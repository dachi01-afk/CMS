<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DetailPenjaminanKunjunganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $records = [];
        $now = now();

        // Ambil ID kunjungan yang penjamin_id-nya BUKAN Pribadi (ID 1) dari tabel kunjungan
        // Karena Penjamin Pribadi biasanya tidak perlu mengisi detail kartu.
        $kunjunganIds = DB::table('kunjungan')
            ->where('penjamin_id', '!=', 1)
            ->pluck('id_kunjungan')
            ->toArray();

        // Jika tidak ada data kunjungan yang menggunakan penjamin selain Pribadi, hentikan seeder.
        if (empty($kunjunganIds)) {
            echo "Tidak ada Kunjungan yang menggunakan Penjamin selain 'Pribadi'. Seeder detail penjaminan dihentikan.\n";
            return;
        }

        // Ambil data penjamin untuk referensi
        $penjamins = DB::table('data_penjamin')->pluck('tipe_penjamin', 'id_penjamin');

        foreach ($kunjunganIds as $kunjunganId) {
            // Ambil penjamin_id dari tabel kunjungan
            $penjaminId = DB::table('kunjungan')->where('id_kunjungan', $kunjunganId)->value('penjamin_id');
            $tipePenjamin = $penjamins[$penjaminId] ?? 'Pribadi';

            $nomorKartu = null;
            $namaPemegangKartu = null;
            $tanggalBerlaku = null;
            $catatan = null;

            // Logika pengisian detail berdasarkan Tipe Penjamin
            if ($tipePenjamin == 'Pemerintah' || $penjaminId == 2) { // BPJS
                // PERBAIKAN: Ganti randomNumber(13, true) dengan numerify(13 hash)
                $nomorKartu = '000' . $faker->numerify('#############'); // Nomor BPJS 13 digit
                $namaPemegangKartu = $faker->name;
                $tanggalBerlaku = Carbon::now()->addYears(5)->toDateString();
            } elseif ($tipePenjamin == 'Asuransi') {
                // PERBAIKAN: Ganti numerify('##########') dengan numerify
                $nomorKartu = 'PL' . $faker->numerify('##########'); // Nomor Polis Asuransi (10 digit)
                $namaPemegangKartu = $faker->name;
                $tanggalBerlaku = $faker->dateTimeBetween('now', '+2 years')->format('Y-m-d');
                $catatan = 'Klaim Jaminan Rawat Jalan sesuai polis';
            } elseif ($tipePenjamin == 'Perusahaan') {
                // PERBAIKAN: Ganti randomNumber(6) dengan numerify(6 hash)
                $nomorKartu = 'KRY-' . $faker->numerify('######'); // Nomor ID Karyawan (6 digit)
                $namaPemegangKartu = $faker->name;
                $catatan = 'Ditanggung 80% oleh perusahaan';
            }

            // Hanya masukkan record jika ada detail yang terisi (bukan Pribadi)
            if ($nomorKartu) {
                $records[] = [
                    'kunjungan_id' => $kunjunganId,
                    'penjamin_id' => $penjaminId,
                    'nomor_kartu_asuransi' => $nomorKartu,
                    'nama_pemegang_kartu' => $namaPemegangKartu,
                    'tanggal_berlaku' => $tanggalBerlaku,
                    'catatan' => $catatan,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Batasi maksimal 20 record jika ada lebih dari 20 kunjungan Non-Pribadi
        if (count($records) > 20) {
            $records = array_slice($records, 0, 20);
        }

        DB::table('detail_penjaminan_kunjungan')->insert($records);
    }
}
