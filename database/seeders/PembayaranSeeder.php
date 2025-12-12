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
        $this->command?->info('🚀 Membuat data pembayaran berdasarkan EMR...');

        $dataEMRList = EMR::with([
            'kunjungan.pasien',
            'kunjungan.layanan',
            'resep.obat',
        ])->get();

        if ($dataEMRList->isEmpty()) {
            $this->command?->warn('⚠️ Tidak ada data EMR.');
            return;
        }

        $metode = MetodePembayaran::first();
        if (! $metode) {
            $this->command?->warn('⚠️ Metode pembayaran belum ada.');
            return;
        }

        $faker = Faker::create('id_ID');
        $jumlahDibuat = 0;

        foreach ($dataEMRList as $emr) {

            // Skip kalau pembayaran sudah ada
            if (Pembayaran::where('emr_id', $emr->id)->exists()) {
                continue;
            }

            $kunjungan = $emr->kunjungan;
            $pasien    = $kunjungan?->pasien?->nama_pasien ?? 'Pasien Tidak Dikenal';

            // ==========================
            // 1️⃣ HITUNG TOTAL LAYANAN
            // ==========================
            $totalLayanan = ($kunjungan?->layanan ?? collect())->sum(function ($layanan) {
                $harga  = (float) ($layanan->harga_layanan ?? 0);
                $jumlah = (int) ($layanan->pivot->jumlah ?? 1);
                return $harga * $jumlah;
            });

            // ==========================
            // 2️⃣ HITUNG TOTAL OBAT
            // ==========================
            $totalObat = ($emr->resep?->obat ?? collect())->sum(function ($obat) {
                $harga  = (float) (
                    $obat->harga_jual
                    ?? $obat->harga
                    ?? 0
                );
                $jumlah = (int) ($obat->pivot->jumlah ?? 1);
                return $harga * $jumlah;
            });

            $totalTagihan = $totalLayanan + $totalObat;

            if ($totalTagihan <= 0) {
                $this->command?->warn("⏭️ EMR ID {$emr->id} total 0, dilewati.");
                continue;
            }

            // ==========================
            // 3️⃣ STATUS PEMBAYARAN
            // ==========================
            // 70% Sudah Bayar → supaya antrian apotek bisa jalan
            $statusBayar = $faker->boolean(70) ? 'Sudah Bayar' : 'Belum Bayar';

            // ==========================
            // 4️⃣ TANGGAL PEMBAYARAN
            // ==========================
            $tanggalBayar = $statusBayar === 'Sudah Bayar'
                ? Carbon::now()->subDays(rand(0, 5))
                : null;

            // ==========================
            // 5️⃣ SIMPAN PEMBAYARAN
            // ==========================
            Pembayaran::create([
                'emr_id'               => $emr->id,
                'total_tagihan'        => $totalTagihan,
                'kembalian'            => 0,
                'metode_pembayaran_id' => $metode->id,
                'kode_transaksi'       => strtoupper(uniqid('TRX_')),
                'tanggal_pembayaran'   => $tanggalBayar,
                'status'               => $statusBayar,
                'catatan'              => "Tagihan medis untuk {$pasien}",
            ]);

            $jumlahDibuat++;
            $this->command?->info(
                "✅ Pembayaran {$statusBayar} | EMR {$emr->id} | Rp" .
                    number_format($totalTagihan, 0, ',', '.')
            );
        }

        $this->command?->info("🎉 Selesai. Total pembayaran dibuat: {$jumlahDibuat}");
    }
}
