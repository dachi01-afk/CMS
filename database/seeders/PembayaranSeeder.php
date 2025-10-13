<?php

namespace Database\Seeders;

use App\Models\Kunjungan;
use App\Models\MetodePembayaran;
use App\Models\Pasien;
use App\Models\Pembayaran;
use App\Models\Resep;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;

class PembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $faker = Faker::create('id_ID');
        // $dataPasien = Pasien::all();
        // $listStatus = ['Sudah Bayar', 'Belum Bayar'];

        // foreach ($dataPasien as $pasien) {
        //     $jumlahPembayaran = rand(1, 5);
        //     for ($i = 0; $i < $jumlahPembayaran; $i++) {
        //         $status = Arr::random($listStatus);
        //         Pembayaran::create([
        //             'pasien_id' => $pasien->id,
        //             'total_tagihan' => $status === 'Sudah Bayar'
        //                 ? $faker->numberBetween(50000, 1000000)
        //                 : 0,
        //             'status' => $status,
        //             'tanggal_pembayaran' => $faker->dateTimeBetween('-1 years', '-1 day'),
        //         ]);
        //     }
        // }

        // Ambil satu data resep beserta relasi obatnya
        $faker = Faker::create();

        // Ambil satu data resep dengan relasi obatnya
        $dataResep = Resep::with('obat')->first();

        if (!$dataResep || $dataResep->obat->isEmpty()) {
            $this->command->warn('⚠️ Tidak ada data resep atau obat di database. Seeder dibatalkan.');
            return;
        }

        // Hitung total tagihan dari resep_obat
        $totalTagihan = 0;
        foreach ($dataResep->obat as $obat) {
            $hargaObat = $obat->total_harga ?? 0;
            $jumlah = $obat->pivot->jumlah ?? 1;
            $subtotal = $hargaObat * $jumlah;

            // Debug log (biar kelihatan pas seeding)
            $this->command->info("💊 {$obat->nama_obat} x{$jumlah} = {$subtotal}");

            $totalTagihan += $subtotal;
        }

        // Ambil satu data resep dengan relasi obatnya
        $dataKunjungan = Kunjungan::with('layanan')->first();

        if (!$dataKunjungan || $dataKunjungan->layanan->isEmpty()) {
            $this->command->warn('⚠️ Tidak ada data kunjungan atau layanan di database. Seeder dibatalkan.');
            return;
        }

        // Hitung total tagihan dari resep_obat
        $totalTagihanLayanan = 0;
        foreach ($dataKunjungan->layanan as $layanan) {
            $hargaLayanan = $layanan->harga_layanan ?? 0;
            $jumlahLayanan = $layanan->pivot->jumlah ?? 1;
            $subtotalLayanan = $hargaLayanan * $jumlahLayanan;

            // Debug log (biar kelihatan pas seeding)
            $this->command->info("💊 {$layanan->nama_obat} x{$jumlahLayanan} = {$subtotalLayanan}");

            $totalTagihanLayanan += $subtotalLayanan;
        }

        $totalAkhir = $totalTagihan + $totalTagihanLayanan;

        $dataMetodePembayaran = MetodePembayaran::firstOrFail();

        // Buat data pembayaran baru
        Pembayaran::create([
            'emr_id'            => 1, // kamu bisa ubah sesuai data EMR yang ada
            'total_tagihan'     => $totalAkhir,
            'metode_pembayaran_id' => $dataMetodePembayaran->id,
            'kode_transaksi'    => strtoupper(uniqid('TRX_')),
            'status'            => 'Belum Bayar',
        ]);

        $this->command->info('✅ Pembayaran berhasil dibuat dengan total tagihan: ' . $totalAkhir);
    }
}
