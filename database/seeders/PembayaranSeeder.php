<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EMR;
use App\Models\Pembayaran;
use App\Models\MetodePembayaran;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Mulai membuat data pembayaran berdasarkan data EMR yang ada...');

        // Ambil semua data EMR beserta relasi pentingnya
        $dataEMRList = EMR::with([
            'kunjungan.pasien',
            'resep.obat',
        ])->get();

        if ($dataEMRList->isEmpty()) {
            $this->command->warn('⚠️ Tidak ada data EMR yang ditemukan!');
            return;
        }

        // Ambil metode pembayaran pertama (default)
        $metode = MetodePembayaran::first();
        if (!$metode) {
            $this->command->warn('⚠️ Tidak ada data Metode Pembayaran!');
            return;
        }

        $jumlahDibuat = 0;
        $faker = Faker::create('id_ID');

        // Loop setiap EMR dan buat data pembayaran
        foreach ($dataEMRList as $emr) {
            $layananList = $emr?->kunjungan->layanan ?? collect();
            $pasien = $kunjungan?->pasien?->nama_pasien ?? 'Pasien Tidak Dikenal';
            $obatList = $emr->resep?->obat ?? collect();

            // Hitung total layanan
            $totalLayanan = $layananList->sum(function ($layanan) {
                $harga = $layanan->harga_layanan ?? 0;
                $jumlah = $layanan->pivot->jumlah ?? 1;
                return $harga * $jumlah;
            });

            // Hitung total obat
            $totalObat = $obatList->sum(function ($obat) {
                $harga = $obat->total_harga ?? 0;
                $jumlah = $obat->pivot->jumlah ?? 1;
                return $harga * $jumlah;
            });

            $totalTagihan = $totalLayanan + $totalObat;

            // Cek apakah sudah ada pembayaran untuk EMR ini
            $sudahAda = Pembayaran::where('emr_id', $emr->id)->exists();
            if ($sudahAda) {
                $this->command->warn("⏭️ Pembayaran untuk EMR ID {$emr->id} sudah ada, dilewati.");
                continue;
            }

            // 🎯 Buat tanggal acak sepanjang tahun ini (2025)
            // $tanggalAcak = Carbon::create(2025, rand(1, 12), rand(1, 28), rand(7, 20), rand(0, 59), 0);

            // Buat data pembayaran
            Pembayaran::create([
                'emr_id' => $emr->id,
                'total_tagihan' => $totalTagihan,
                'kembalian' => 0,
                'metode_pembayaran_id' => $metode->id,
                'kode_transaksi' => strtoupper(uniqid('TRX_')),
                'tanggal_pembayaran' => $faker->dateTimeBetween('-100 years', '-1 day'),
                'status' => 'Belum Bayar',
                'catatan' => "Tagihan untuk {$pasien}",
            ]);

            $jumlahDibuat++;
            $this->command->info("✅ Pembayaran untuk {$pasien} (EMR ID: {$emr->id}) berhasil dibuat. Total: Rp" . number_format($totalTagihan, 0, ',', '.'));
        }

        $this->command->info("🎉 Selesai! Total pembayaran baru yang dibuat: {$jumlahDibuat}");
    }
}
