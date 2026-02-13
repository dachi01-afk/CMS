<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransaksiPembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini membuat 3 transaksi LENGKAP dari awal sampai pembayaran selesai:
     * 1. Kunjungan (status: Payment/Succeed)
     * 2. EMR (rekam medis)
     * 3. Resep + Resep Obat
     * 4. Pembayaran (status: Sudah Bayar)
     * 5. Pembayaran Detail (itemisasi)
     */
    public function run(): void
    {
        $today = Carbon::now();
        
        // Ambil data master yang dibutuhkan
        $pasien = DB::table('pasien')->first();
        $dokter = DB::table('dokter')->first();
        $poli = DB::table('poli')->first();
        $obat = DB::table('obat')->limit(3)->get();
        $layanan = DB::table('layanan')->limit(2)->get();
        $metodePembayaran = DB::table('metode_pembayaran')->first();

        if (!$pasien || !$dokter || !$poli) {
            $this->command->error('❌ Data master (pasien/dokter/poli) tidak ditemukan!');
            $this->command->line('💡 Jalankan seeder master terlebih dahulu.');
            return;
        }

        // Data transaksi yang akan dibuat
        $transaksiData = [
            [
                'tanggal' => $today->copy()->subDays(7),
                'keluhan' => 'Demam tinggi dan batuk',
                'diagnosis' => 'ISPA (Infeksi Saluran Pernapasan Akut)',
                'jumlah_obat' => 2,
                'total_tagihan' => 150000,
            ],
            [
                'tanggal' => $today->copy()->subDays(3),
                'keluhan' => 'Sakit kepala berkepanjangan',
                'diagnosis' => 'Tension Headache',
                'jumlah_obat' => 3,
                'total_tagihan' => 200000,
            ],
            [
                'tanggal' => $today->copy()->subDays(1),
                'keluhan' => 'Nyeri lambung dan mual',
                'diagnosis' => 'Gastritis',
                'jumlah_obat' => 2,
                'total_tagihan' => 175000,
            ],
        ];

        foreach ($transaksiData as $index => $data) {
            DB::transaction(function () use ($data, $pasien, $dokter, $poli, $obat, $layanan, $metodePembayaran, $index) {
                
                $noAntrian = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                $kodeTransaksi = 'TRX-' . $data['tanggal']->format('Ymd') . '-' . $noAntrian;

                // ========================================
                // 1️⃣ KUNJUNGAN (Status: Succeed)
                // ========================================
                $kunjunganId = DB::table('kunjungan')->insertGetId([
                    'poli_id' => $poli->id,
                    'pasien_id' => $pasien->id,
                    'dokter_id' => $dokter->id,
                    'jadwal_dokter_id' => null,
                    'tanggal_kunjungan' => $data['tanggal']->toDateString(),
                    'no_antrian' => $noAntrian,
                    'keluhan_awal' => $data['keluhan'],
                    'status' => 'Succeed', // ✅ Transaksi sudah selesai
                    'created_at' => $data['tanggal'],
                    'updated_at' => $data['tanggal'],
                ]);

                // ========================================
                // 2️⃣ RESEP (Status: done)
                // ========================================
                $resepId = DB::table('resep')->insertGetId([
                    'kunjungan_id' => $kunjunganId,
                    'status' => 'done', // ✅ Resep sudah diambil
                    'created_at' => $data['tanggal'],
                    'updated_at' => $data['tanggal'],
                ]);

                // ========================================
                // 3️⃣ EMR (Rekam Medis)
                // ========================================
                $emrId = DB::table('emr')->insertGetId([
                    'kunjungan_id' => $kunjunganId,
                    'pasien_id' => $pasien->id,
                    'dokter_id' => $dokter->id,
                    'poli_id' => $poli->id,
                    'perawat_id' => null,
                    'resep_id' => $resepId,
                    'order_lab_id' => null,
                    'keluhan_utama' => $data['keluhan'],
                    'riwayat_penyakit_dahulu' => null,
                    'riwayat_penyakit_keluarga' => null,
                    'tekanan_darah' => '120/80',
                    'suhu_tubuh' => 37.5,
                    'nadi' => 80,
                    'pernapasan' => 20,
                    'saturasi_oksigen' => 98,
                    'tinggi_badan' => 165.00,
                    'berat_badan' => 60.00,
                    'imt' => 22.04,
                    'diagnosis' => $data['diagnosis'],
                    'created_at' => $data['tanggal'],
                    'updated_at' => $data['tanggal'],
                ]);

                // ========================================
                // 4️⃣ RESEP OBAT (Detail obat yang diresepkan)
                // ========================================
                $totalHargaObat = 0;
                $resepObatIds = [];

                foreach ($obat->take($data['jumlah_obat']) as $idx => $item) {
                    $jumlah = rand(1, 3);
                    $hargaObat = $item->total_harga * $jumlah;
                    $totalHargaObat += $hargaObat;

                    $resepObatId = DB::table('resep_obat')->insertGetId([
                        'resep_id' => $resepId,
                        'obat_id' => $item->id,
                        'jumlah' => $jumlah,
                        'dosis' => $item->dosis ?? 1.00,
                        'keterangan' => '3x sehari sesudah makan',
                        'created_at' => $data['tanggal'],
                        'updated_at' => $data['tanggal'],
                    ]);

                    $resepObatIds[] = $resepObatId;
                }

                // ========================================
                // 5️⃣ PEMBAYARAN (Header)
                // ========================================
                $diskonPersen = rand(0, 10); // diskon 0-10%
                $diskonNominal = ($data['total_tagihan'] * $diskonPersen) / 100;
                $totalSetelahDiskon = $data['total_tagihan'] - $diskonNominal;
                $uangDiterima = ceil($totalSetelahDiskon / 10000) * 10000; // pembulatan ke atas 10rb
                $kembalian = $uangDiterima - $totalSetelahDiskon;

                $pembayaranId = DB::table('pembayaran')->insertGetId([
                    'emr_id' => $emrId,
                    'kode_transaksi' => $kodeTransaksi,
                    'metode_pembayaran_id' => $metodePembayaran->id ?? null,
                    'diskon_tipe' => $diskonPersen > 0 ? 'persen' : null,
                    'diskon_nilai' => $diskonNominal,
                    'total_setelah_diskon' => $totalSetelahDiskon,
                    'uang_yang_diterima' => $uangDiterima,
                    'kembalian' => $kembalian,
                    'tanggal_pembayaran' => $data['tanggal'],
                    'status' => 'Sudah Bayar', // ✅ Pembayaran sudah lunas
                    'bukti_pembayaran' => null,
                    'catatan' => 'Pembayaran lunas pada ' . $data['tanggal']->format('d/m/Y'),
                    'created_at' => $data['tanggal'],
                    'updated_at' => $data['tanggal'],
                ]);

                // ========================================
                // 6️⃣ PEMBAYARAN DETAIL (Itemisasi)
                // ========================================
                
                // Item 1: Biaya Konsultasi Dokter
                DB::table('pembayaran_detail')->insert([
                    'pembayaran_id' => $pembayaranId,
                    'layanan_id' => $layanan->first()->id ?? null,
                    'resep_obat_id' => null,
                    'order_lab_detail_id' => null,
                    'order_radiologi_detail_id' => null,
                    'nama_item' => 'Konsultasi Dokter ' . ($dokter->nama_dokter ?? 'Umum'),
                    'qty' => 1,
                    'harga' => 50000,
                    'subtotal' => 50000,
                    'created_at' => $data['tanggal'],
                    'updated_at' => $data['tanggal'],
                ]);

                // Item 2-N: Obat-obatan dari resep
                foreach ($resepObatIds as $idx => $resepObatId) {
                    $resepObat = DB::table('resep_obat')->where('id', $resepObatId)->first();
                    $obatItem = DB::table('obat')->where('id', $resepObat->obat_id)->first();
                    
                    $subtotal = $obatItem->total_harga * $resepObat->jumlah;

                    DB::table('pembayaran_detail')->insert([
                        'pembayaran_id' => $pembayaranId,
                        'layanan_id' => null,
                        'resep_obat_id' => $resepObatId,
                        'order_lab_detail_id' => null,
                        'order_radiologi_detail_id' => null,
                        'nama_item' => $obatItem->nama_obat,
                        'qty' => $resepObat->jumlah,
                        'harga' => $obatItem->total_harga,
                        'subtotal' => $subtotal,
                        'created_at' => $data['tanggal'],
                        'updated_at' => $data['tanggal'],
                    ]);
                }

                $this->command->info("✅ Transaksi #{$noAntrian} berhasil dibuat!");
                $this->command->line("   📅 Tanggal: " . $data['tanggal']->format('d/m/Y'));
                $this->command->line("   👤 Pasien: {$pasien->nama_pasien}");
                $this->command->line("   💊 Diagnosis: {$data['diagnosis']}");
                $this->command->line("   💰 Total Bayar: Rp " . number_format($totalSetelahDiskon, 0, ',', '.'));
                $this->command->line("   🎫 Kode Transaksi: {$kodeTransaksi}");
                $this->command->line("");
            });
        }

        $this->command->info("🎉 Seeder transaksi pembayaran selesai!");
        $this->command->line("📊 Total transaksi yang dibuat: " . count($transaksiData));
    }
}