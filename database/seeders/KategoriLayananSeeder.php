<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriLayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('kategori_layanan')->insert([
            // --- Kategori dasar yang sudah ada ---
            [
                'nama_kategori'       => 'Pemeriksaan',
                'deskripsi_kategori'  => 'Kategori layanan ini adalah kategori yang khusus untuk melakukan pemeriksaan pasien (anamnesis, pemeriksaan fisik, dan penegakan diagnosis).',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Non Pemeriksaan',
                'deskripsi_kategori'  => 'Kategori layanan ini adalah kategori yang tidak berhubungan langsung dengan pemeriksaan medis pasien.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],

            // --- Konsultasi / Pemeriksaan Dokter ---
            [
                'nama_kategori'       => 'Konsultasi Dokter Umum',
                'deskripsi_kategori'  => 'Layanan konsultasi dan pemeriksaan oleh dokter umum (rawat jalan).',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Konsultasi Dokter Spesialis',
                'deskripsi_kategori'  => 'Layanan konsultasi dan pemeriksaan oleh dokter spesialis (penyakit dalam, anak, obgyn, saraf, dan lainnya).',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Konsultasi Dokter Gigi',
                'deskripsi_kategori'  => 'Layanan konsultasi dan pemeriksaan kesehatan gigi dan mulut oleh dokter gigi.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Konsultasi Kebidanan & Kehamilan',
                'deskripsi_kategori'  => 'Layanan pemeriksaan ibu hamil dan konsultasi kebidanan oleh dokter spesialis kandungan atau bidan.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],

            // --- Tindakan Medis / Keperawatan ---
            [
                'nama_kategori'       => 'Tindakan Medis Rawat Jalan',
                'deskripsi_kategori'  => 'Berbagai tindakan medis ringan di poli atau klinik, seperti injeksi, nebulizer, pemasangan infus, dan sejenisnya.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Tindakan Keperawatan',
                'deskripsi_kategori'  => 'Layanan tindakan keperawatan seperti perawatan luka, pemasangan kateter, perawatan stoma, dan lain-lain.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Tindakan Gawat Darurat (IGD)',
                'deskripsi_kategori'  => 'Layanan tindakan emergensi di Instalasi Gawat Darurat untuk pasien dengan kondisi akut atau kritis.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Tindakan Operasi Minor',
                'deskripsi_kategori'  => 'Tindakan bedah minor yang bisa dilakukan di klinik atau ruang tindakan, seperti incisi abses, penjahitan luka kecil, dan sejenisnya.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],

            // --- Penunjang Medis ---
            [
                'nama_kategori'       => 'Laboratorium',
                'deskripsi_kategori'  => 'Seluruh layanan pemeriksaan laboratorium seperti hematologi, kimia darah, urin, faeses, dan panel laboratorium lainnya.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Radiologi',
                'deskripsi_kategori'  => 'Layanan penunjang radiologi seperti foto Rontgen, USG, CT-Scan, dan pemeriksaan imaging lainnya.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Rehabilitasi Medik & Fisioterapi',
                'deskripsi_kategori'  => 'Layanan fisioterapi, rehabilitasi medik, terapi latihan, dan terapi fisik lainnya.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Gizi Klinik & Konseling',
                'deskripsi_kategori'  => 'Layanan konseling gizi, edukasi nutrisi, dan penatalaksanaan diet pasien.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],

            // --- Farmasi / Obat / Alkes ---
            [
                'nama_kategori'       => 'Obat Resep',
                'deskripsi_kategori'  => 'Layanan penyiapan dan penyerahan obat berdasarkan resep dokter, termasuk racikan.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Obat Bebas & OTC',
                'deskripsi_kategori'  => 'Layanan penjualan obat bebas, obat bebas terbatas, vitamin, dan suplemen tanpa resep dokter.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Alat Kesehatan & BHP',
                'deskripsi_kategori'  => 'Penjualan atau pemakaian alat kesehatan dan bahan habis pakai (BHP) seperti spuit, kasa, sarung tangan, dan lain-lain.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],

            // --- Administrasi & Non-Medis ---
            [
                'nama_kategori'       => 'Pendaftaran & Registrasi',
                'deskripsi_kategori'  => 'Biaya administrasi pendaftaran pasien baru, registrasi kunjungan ulang, dan nomor rekam medis.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Administrasi & Dokumen',
                'deskripsi_kategori'  => 'Layanan administratif seperti surat keterangan sehat, surat rujukan, resume medis, dan dokumen medis lainnya.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Biaya Kamar & Ruang Perawatan',
                'deskripsi_kategori'  => 'Biaya penggunaan kamar rawat inap, ruang perawatan, ICU, HCU, atau ruang khusus lainnya.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],

            // --- Paket Layanan / MCU ---
            [
                'nama_kategori'       => 'Paket Medical Check Up (MCU)',
                'deskripsi_kategori'  => 'Paket pemeriksaan kesehatan menyeluruh (MCU) yang berisi kombinasi konsultasi, lab, radiologi, dan layanan lain.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Paket Kehamilan & Persalinan',
                'deskripsi_kategori'  => 'Paket layanan antenatal care, persalinan, dan layanan pasca persalinan sesuai kebijakan klinik/RS.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Paket Tindakan / Operasi',
                'deskripsi_kategori'  => 'Paket biaya tindakan atau operasi tertentu yang sudah mencakup beberapa komponen layanan.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
        ]);
    }
}
