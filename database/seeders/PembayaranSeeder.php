<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EMR;
use App\Models\Pembayaran;
use App\Models\PembayaranDetail;
use App\Models\MetodePembayaran;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('🚀 Membuat data pembayaran + detail...');

        $emrList = EMR::with([
            'kunjungan.pasien',
            'kunjungan.layanan',
            'resep.obat',
        ])->get();

        if ($emrList->isEmpty()) {
            $this->command?->warn('⚠️ Tidak ada EMR.');
            return;
        }

        $metode = MetodePembayaran::first();
        if (! $metode) {
            $this->command?->warn('⚠️ Metode pembayaran belum ada.');
            return;
        }

        $faker = Faker::create('id_ID');
        $jumlah = 0;

        foreach ($emrList as $emr) {

            if (Pembayaran::where('emr_id', $emr->id)->exists()) {
                continue;
            }

            $status = $faker->boolean(70) ? 'Sudah Bayar' : 'Belum Bayar';

            $pembayaran = Pembayaran::create([
                'emr_id'               => $emr->id,
                'kode_transaksi'       => strtoupper(Str::random(10)),
                'tanggal_pembayaran'   => $status === 'Sudah Bayar'
                    ? Carbon::now()->subDays(rand(0, 5))
                    : null,
                'status'               => $status,
                'metode_pembayaran_id' => $metode->id,
                'total_tagihan'        => 0, // sementara
                'kembalian'            => 0,
                'catatan'              => 'Seeder pembayaran otomatis',
            ]);

            $total = 0;

            // =========================
            // LAYANAN
            // =========================
            foreach ($emr->kunjungan->layanan ?? [] as $layanan) {

                $harga  = (float) ($layanan->harga_setelah_diskon ?? 0);
                $qty    = (int) ($layanan->pivot->jumlah ?? 1);
                $subtotal = $harga * $qty;

                PembayaranDetail::create([
                    'pembayaran_id' => $pembayaran->id,
                    'layanan_id'    => $layanan->id,
                    'nama_item'     => 'Layanan: ' . $layanan->nama_layanan,
                    'qty'           => $qty,
                    'harga'         => $harga,
                    'subtotal'      => $subtotal,
                ]);

                $total += $subtotal;
            }

            // =========================
            // OBAT
            // =========================
            foreach ($emr->resep?->obat ?? [] as $obat) {

                $harga  = (float) ($obat->harga_jual_obat ?? 0);
                $qty    = (int) ($obat->pivot->jumlah ?? 1);
                $subtotal = $harga * $qty;

                PembayaranDetail::create([
                    'pembayaran_id' => $pembayaran->id,
                    'resep_obat_id' => $obat->pivot->id ?? null,
                    'nama_item'     => 'Obat: ' . $obat->nama_obat,
                    'qty'           => $qty,
                    'harga'         => $harga,
                    'subtotal'      => $subtotal,
                ]);

                $total += $subtotal;
            }

            // =========================
            // UPDATE TOTAL
            // =========================
            $pembayaran->update([
                'total_tagihan' => $total,
            ]);

            $jumlah++;
            $this->command?->info("✅ EMR {$emr->id} | Total Rp " . number_format($total, 0, ',', '.'));
        }

        $this->command?->info("🎉 Selesai. Total dibuat: {$jumlah}");
    }
}
