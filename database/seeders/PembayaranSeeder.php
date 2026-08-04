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

// LAB
use App\Models\OrderLab;
use App\Models\OrderLabDetail;
use App\Models\JenisPemeriksaanLab;

// RADIOLOGI
use App\Models\OrderRadiologi;
use App\Models\OrderRadiologiDetail;
use App\Models\JenisPemeriksaanRadiologi;

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('🚀 Membuat data pembayaran + detail (layanan, obat, lab, radiologi)...');

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

            // pastikan punya kunjungan_id untuk ngelink order lab/radiologi
            $kunjunganId = $emr->kunjungan_id ?? $emr->kunjungan?->id;
            if (! $kunjunganId) {
                $this->command?->warn("⚠️ EMR {$emr->id} tidak punya kunjungan_id, skip.");
                continue;
            }

            $status = $faker->boolean(20) ? 'Sudah Bayar' : 'Belum Bayar';

            $pembayaran = Pembayaran::create([
                'emr_id'               => $emr->id,
                'kode_transaksi'       => strtoupper(Str::random(10)),
                'tanggal_pembayaran'   => $status === 'Sudah Bayar'
                    ? Carbon::now()->subDays(rand(0, 5))
                    : null,
                'status'               => $status,
                'metode_pembayaran_id' => $metode->id,
                'total_tagihan'        => 0,
                'kembalian'            => 0,
                'catatan'              => 'Seeder pembayaran otomatis',
            ]);

            $total = 0;

            // =========================
            // LAYANAN
            // =========================
            foreach ($emr->kunjungan->layanan ?? [] as $layanan) {

                $harga    = (float) ($layanan->harga_setelah_diskon ?? 0);
                $qty      = (int) ($layanan->pivot->jumlah ?? 1);
                $subtotal = $harga * $qty;

                PembayaranDetail::create([
                    'pembayaran_id' => $pembayaran->id,
                    'layanan_id'    => $layanan->id,
                    'nama_item'     => 'Layanan: ' . ($layanan->nama_layanan ?? '-'),
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

                $harga    = (float) ($obat->harga_jual_obat ?? 0);
                $qty      = (int) ($obat->pivot->jumlah ?? 1);
                $subtotal = $harga * $qty;

                PembayaranDetail::create([
                    'pembayaran_id' => $pembayaran->id,
                    'resep_obat_id' => $obat->pivot->id ?? null,
                    'nama_item'     => 'Obat: ' . ($obat->nama_obat ?? '-'),
                    'qty'           => $qty,
                    'harga'         => $harga,
                    'subtotal'      => $subtotal,
                ]);

                $total += $subtotal;
            }

            // =========================
            // LAB: order_lab -> order_lab_detail
            // isi kolom: order_lab_detail_id
            // =========================
            $orderLabs = OrderLab::where('kunjungan_id', $kunjunganId)->get();

            foreach ($orderLabs as $orderLab) {
                $labDetails = OrderLabDetail::where('order_lab_id', $orderLab->id)->get();

                foreach ($labDetails as $detail) {
                    $jenisLab = JenisPemeriksaanLab::find($detail->jenis_pemeriksaan_lab_id);
                    if (! $jenisLab) continue;

                    $harga    = (float) ($jenisLab->harga_pemeriksaan_lab ?? 0);
                    $qty      = 1;
                    $subtotal = $harga * $qty;

                    PembayaranDetail::create([
                        'pembayaran_id'       => $pembayaran->id,
                        'order_lab_detail_id' => $detail->id, // ✅ TERISI
                        'nama_item'           => 'Lab: ' . ($jenisLab->nama_pemeriksaan ?? '-'),
                        'qty'                 => $qty,
                        'harga'               => $harga,
                        'subtotal'            => $subtotal,
                    ]);

                    $total += $subtotal;
                }
            }

            // =========================
            // RADIOLOGI: order_radiologi -> order_radiologi_detail
            // isi kolom: order_radiologi_detail_id
            // =========================
            $orderRads = OrderRadiologi::where('kunjungan_id', $kunjunganId)->get();

            foreach ($orderRads as $orderRadiologi) {
                $radDetails = OrderRadiologiDetail::where('order_radiologi_id', $orderRadiologi->id)->get();

                foreach ($radDetails as $detail) {
                    $jenisRad = JenisPemeriksaanRadiologi::find($detail->jenis_pemeriksaan_radiologi_id);
                    if (! $jenisRad) continue;

                    $harga    = (float) ($jenisRad->harga_pemeriksaan_radiologi ?? 0);
                    $qty      = 1;
                    $subtotal = $harga * $qty;

                    PembayaranDetail::create([
                        'pembayaran_id'             => $pembayaran->id,
                        'order_radiologi_detail_id' => $detail->id, // ✅ TERISI
                        'nama_item'                 => 'Radiologi: ' . ($jenisRad->nama_pemeriksaan ?? '-'),
                        'qty'                       => $qty,
                        'harga'                     => $harga,
                        'subtotal'                  => $subtotal,
                    ]);

                    $total += $subtotal;
                }
            }

            // =========================
            // UPDATE TOTAL
            // =========================
            $uangDiterima = null;
            $kembalian = 0;

            if ($status === 'Sudah Bayar') {
                $uangDiterima = $total + $faker->numberBetween(0, 50000);
                $kembalian = max(0, $uangDiterima - $total);
            }

            $pembayaran->update([
                'total_tagihan'      => $total,
                'total_setelah_diskon' => $total, // opsional biar rapih
                'uang_yang_diterima' => $uangDiterima,
                'kembalian'          => $kembalian,
            ]);

            $jumlah++;
            $this->command?->info("✅ EMR {$emr->id} | Total Rp " . number_format($total, 0, ',', '.'));
        }

        $this->command?->info("🎉 Selesai. Total dibuat: {$jumlah}");
    }
}
