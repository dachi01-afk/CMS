<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EMR;
use App\Models\Pembayaran;
use App\Models\MetodePembayaran;
use App\Models\Resep;

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil data EMR beserta kunjungan dan layanan
        $dataEMR = EMR::with('kunjungan.layanan')->first();
        // dd($dataEMR);

        if (!$dataEMR) {
            $this->command->warn('⚠️ Tidak ada data EMR yang ditemukan!');
            return;
        }

        $dataKunjungan = $dataEMR->kunjungan;
        $dataKunjunganLayanan = $dataKunjungan->layanan ?? collect();

        // ======================================================
        // 💰 Hitung total tagihan dari layanan
        // ======================================================
        $totalTagihanLayanan = 0;

        foreach ($dataKunjunganLayanan as $layanan) {
            $hargaLayanan = $layanan->harga_layanan ?? 0;
            $jumlahLayanan = $layanan->pivot->jumlah ?? 1;
            $subtotalLayanan = $hargaLayanan * $jumlahLayanan;

            $this->command->info("🩺 {$layanan->nama_layanan} x{$jumlahLayanan} = Rp{$subtotalLayanan}");
            $totalTagihanLayanan += $subtotalLayanan;
        }

        // ======================================================
        // 💊 Hitung total tagihan dari resep & obat
        // ======================================================
        $dataResep = Resep::with('obat')->first();

        $totalTagihanObat = 0;


        foreach ($dataResep->obat as $obat) {
            $hargaObat = $obat->total_harga ?? 0;
            $jumlahObat = $obat->pivot->jumlah ?? 1;
            $subtotalObat = $hargaObat * $jumlahObat;

            $this->command->info("💊 {$obat->nama_obat} x{$jumlahObat} = Rp{$subtotalObat}");
            $totalTagihanObat += $subtotalObat;
        }


        // ======================================================
        // 💵 Total keseluruhan
        // ======================================================
        $totalTagihanAkhir = $totalTagihanLayanan + $totalTagihanObat;

        // ======================================================
        // 💳 Buat data pembayaran
        // ======================================================
        $metode = MetodePembayaran::first();
        if (!$metode) {
            $this->command->warn('⚠️ Tidak ada data Metode Pembayaran!');
            return;
        }

        Pembayaran::create([
            'emr_id' => $dataEMR->id,
            'total_tagihan' => $totalTagihanAkhir,
            'metode_pembayaran_id' => $metode->id,
            'kode_transaksi' => strtoupper(uniqid('TRX_')),
            'status' => 'Belum Bayar',
        ]);

        $this->command->info("✅ Pembayaran berhasil dibuat!");
        $this->command->info("💰 Total Layanan: Rp" . number_format($totalTagihanLayanan, 0, ',', '.'));
        $this->command->info("💊 Total Obat: Rp" . number_format($totalTagihanObat, 0, ',', '.'));
        $this->command->info("💵 Total Akhir: Rp" . number_format($totalTagihanAkhir, 0, ',', '.'));
    }
}
