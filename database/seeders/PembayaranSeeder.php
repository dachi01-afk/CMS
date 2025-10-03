<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Kunjungan;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $records = [];
        $now = now();

        // Daftar data transaksi yang mungkin
        $metodeTransaksiList = [
            'Tunai' => ['count' => 8, 'bank' => null, 'kartu' => null],
            'Kartu Debit' => ['count' => 4, 'bank' => ['BCA', 'Mandiri', 'BNI'], 'kartu' => ['VISA', 'GPN']],
            'Kartu Kredit' => ['count' => 3, 'bank' => ['BRI', 'CIMB Niaga'], 'kartu' => ['VISA', 'Mastercard']],
            'Transfer Bank' => ['count' => 3, 'bank' => ['BCA', 'Mandiri'], 'kartu' => null],
            'Virtual Account' => ['count' => 2, 'bank' => ['GOPAY', 'DANA'], 'kartu' => null],
        ];

        $kunjunganIds = range(1, 20); // Asumsi ada 20 data kunjungan
        shuffle($kunjunganIds); // Acak urutan kunjungan

        $counter = 0;

        foreach ($metodeTransaksiList as $metode => $data) {
            for ($i = 0; $i < $data['count'] && $counter < 20; $i++) {
                $kunjunganId = array_pop($kunjunganIds); // Ambil ID kunjungan unik

                // Menentukan waktu pembayaran (setelah waktu kunjungan)
                $tanggalPembayaran = $faker->dateTimeBetween('-4 days', 'now');

                $nomorReferensi = null;
                $namaBank = null;
                $jenisKartu = null;

                if ($metode !== 'Tunai') {
                    // Isi detail untuk transaksi non-tunai
                    $nomorReferensi = $faker->bothify('TX#######??#####');
                    $namaBank = $faker->randomElement($data['bank']);

                    if ($metode === 'Kartu Debit' || $metode === 'Kartu Kredit') {
                        $jenisKartu = $faker->randomElement($data['kartu']);
                    }
                }

                $records[] = [
                    'kunjungan_id' => $kunjunganId,
                    'tanggal_pembayaran' => $tanggalPembayaran,
                    'total_biaya' => $faker->randomFloat(2, 50000, 3000000), // Biaya antara 50 ribu - 3 juta
                    'waktu_obat_diserahkan' => ($faker->boolean(70) && $metodeTransaksiList['Tunai']['count'] < 19) // 70% sudah diserahkan
                        ? Carbon::parse($tanggalPembayaran)->addMinutes($faker->numberBetween(5, 30))
                        : null,
                    'metode_transaksi' => $metode,
                    'nomor_referensi_bank' => $nomorReferensi,
                    'nama_bank' => $namaBank,
                    'jenis_kartu' => $jenisKartu,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $counter++;
            }
        }

        DB::table('pembayaran')->insert($records);
    }
}
