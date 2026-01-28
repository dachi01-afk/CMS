<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\KategoriLayanan;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil kategori
        $kategoriPemeriksaan = KategoriLayanan::where('nama_kategori', 'Pemeriksaan')->first();
        $kategoriNonPemeriksaan = KategoriLayanan::where('nama_kategori', 'Non Pemeriksaan')->first();

        if (! $kategoriPemeriksaan || ! $kategoriNonPemeriksaan) {
            $this->command?->error('Kategori "Pemeriksaan" atau "Non Pemeriksaan" belum ada. Jalankan KategoriLayananSeeder dulu.');
            return;
        }

        // Kolom tabel layanan yang benar (real dari DB)
        $cols = Schema::getColumnListing('layanan');

        // Deteksi kolom harga “sebelum”
        $hargaBeforeCol = null;
        foreach (['harga_sebelum_diskon', 'harga_layanan', 'harga', 'tarif', 'biaya'] as $c) {
            if (in_array($c, $cols, true)) { $hargaBeforeCol = $c; break; }
        }

        // Deteksi kolom diskon & harga setelah diskon
        $diskonCol = in_array('diskon', $cols, true) ? 'diskon' : null;
        $hargaAfterCol = in_array('harga_setelah_diskon', $cols, true) ? 'harga_setelah_diskon' : null;

        if ($hargaBeforeCol === null) {
            $this->command?->error("Tabel layanan tidak punya kolom harga (harga_sebelum_diskon / harga_layanan / harga / tarif / biaya).");
            return;
        }

        // Data layanan (diskon default 0)
        $list = [
            // Pemeriksaan
            [
                'nama_layanan' => 'Konsultasi / pemeriksaan saja',
                'harga' => 50000,
                'diskon' => 0,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],
            [
                'nama_layanan' => 'Scaling / pembersihan karang gigi',
                'harga' => 200000,
                'diskon' => 0,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],
            [
                'nama_layanan' => 'Tambal gigi',
                'harga' => 350000,
                'diskon' => 0,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],
            [
                'nama_layanan' => 'Pencabutan gigi sederhana',
                'harga' => 150000,
                'diskon' => 0,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],
            [
                'nama_layanan' => 'Perawatan saluran akar (endodontik)',
                'harga' => 300000,
                'diskon' => 0,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],

            // Non Pemeriksaan
            [
                'nama_layanan' => 'Pendaftaran Pasien Baru',
                'harga' => 10000,
                'diskon' => 0,
                'kategori_layanan_id' => $kategoriNonPemeriksaan->id,
            ],
            [
                'nama_layanan' => 'Pembuatan / Cetak Ulang Kartu Berobat',
                'harga' => 15000,
                'diskon' => 0,
                'kategori_layanan_id' => $kategoriNonPemeriksaan->id,
            ],
            [
                'nama_layanan' => 'Cetak Ulang Rekam Medis / Resume Medis',
                'harga' => 20000,
                'diskon' => 0,
                'kategori_layanan_id' => $kategoriNonPemeriksaan->id,
            ],
        ];

        foreach ($list as $row) {
            $harga = (float) $row['harga'];
            $diskon = (float) ($row['diskon'] ?? 0);
            $hargaAfter = max($harga - $diskon, 0);

            $insert = [
                'nama_layanan' => $row['nama_layanan'],
                'kategori_layanan_id' => $row['kategori_layanan_id'],
                $hargaBeforeCol => $harga,
            ];

            // isi diskon kalau kolomnya ada
            if ($diskonCol) {
                $insert[$diskonCol] = $diskon;
            }

            // isi harga_setelah_diskon kalau kolomnya ada
            if ($hargaAfterCol) {
                $insert[$hargaAfterCol] = $hargaAfter;
            }

            // timestamps kalau ada
            if (in_array('created_at', $cols, true)) $insert['created_at'] = now();
            if (in_array('updated_at', $cols, true)) $insert['updated_at'] = now();

            DB::table('layanan')->insert($insert);
        }

        $this->command?->info("Seeder layanan sukses. harga_before={$hargaBeforeCol}, diskon=" . ($diskonCol ?? '-') . ", harga_after=" . ($hargaAfterCol ?? '-'));
    }
}
