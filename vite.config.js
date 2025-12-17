import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",

                // admin
                "resources/js/admin/dashboard.js",

                // Jenis spesialis dokter
                "resources/js/admin/jenisSpesialisDokter/data-jenis-spesialis-dokter.js",

                // poli
                "resources/js/admin/poli/data-poli.js",

                // Kategori Layanan
                "resources/js/admin/kategoriLayanan/data-kategori-layanan.js",

                // layanan
                "resources/js/admin/poli/data-poli.js",
                "resources/js/admin/layanan/data-layanan.js",

                // manajemen-pengguna
                "resources/js/admin/manajemenPengguna/data_pengguna.js",
                "resources/js/admin/manajemenPengguna/data_dokter.js",
                "resources/js/admin/manajemenPengguna/data_farmasi.js",
                "resources/js/admin/manajemenPengguna/data_perawat.js",
                "resources/js/admin/manajemenPengguna/data_kasir.js",
                "resources/js/admin/manajemenPengguna/data_pasien.js",
                "resources/js/admin/pengambilanObat/pengambilan_obat.js",
                "resources/js/admin/jadwalKunjungan/jadwal_kunjungan.js",
                "resources/js/admin/jadwalKunjungan/proses_kunjungan.js",

                // jadwal kunjungan
                "resources/js/admin/jadwalKunjungan/jadwal-dokter-yang-akan-datang.js",
                "resources/js/admin/jadwalKunjungan/kunjungan-masa-depan.js",

                // pengaturan klinik
                "resources/js/admin/pengaturanKlinik/jadwal_dokter.js",
                "resources/js/admin/pengaturanKlinik/daftar_obat.js",
                "resources/js/admin/laporan/laporan_kunjungan.js",
                "resources/js/admin/laporan/laporan_keuangan.js",
                "resources/js/admin/laporan/laporan_resep_dan_apotek.js",
                "resources/js/admin/laporan/laporan_administrasi.js",
                "resources/js/admin/dataMedisPasien/rekam_medis_elektronik.js",
                "resources/js/admin/dataMedisPasien/data_diagnosa_dan_konsultasi.js",
                "resources/js/admin/dataMedisPasien/data_hasil_lab.js",

                // Order Layanan
                "resources/js/admin/order-layanan/data-order-layanan.js",

                // Start Role Kasir
                // transaksi
                "resources/js/kasir/pembayaran/transaksi-menunggu.js",
                "resources/js/kasir/riwayat-transaksi/riwayat-transaksi.js",

                // Metode Pembayaran
                "resources/js/kasir/metode-pembayaran/metode-pembayaran.js",

                // Transaksi Layanan
                "resources/js/kasir/pembayaran/data-transaksi-layanan.js",

                // transaksi obat
                "resources/js/kasir/dashboard.js",
                "resources/js/kasir/pembayaran/transaksi-obat.js",
                "resources/js/farmasi/obat/data-obat.js",
                "resources/js/farmasi/obat/order-obat.js",

                // Riwyat Transaksi Obat
                "resources/js/kasir/pembayaran/riwayat-transaksi-obat.js",

                // Riwayat Transaksi Layanan
                "resources/js/kasir/riwayat-transaksi/data-riwayat-transaksi-layanan.js",

                // End Role Kasir

                // Role Perawat
                "resources/js/perawat/dashboard.js",
                "resources/js/perawat/kunjungan/data-kunjungan-hari-ini.js",
                "resources/js/perawat/kunjungan/data-triage-pasien.js",
                "resources/js/perawat/kunjungan/form-pengisian-vital-sign-pasien.js",

                // Role Farmasi
                "resources/js/farmasi/dashboard.js",
                "resources/js/farmasi/pengambilan-obat/data-pengambilan-obat.js",
                "resources/js/farmasi/kategori-obat/data-kategori-obat.js",
                "resources/js/farmasi/penggunaan-obat/data-penggunaan-obat.js",
                "resources/js/farmasi/kadaluarsa-obat/data-kadaluarsa-obat.js",
                "resources/js/farmasi/bahan-habis-pakai/data-bahan-habis-pakai.js",
                "resources/js/farmasi/kadaluarsa-bhp/data-kadaluarsa-bhp.js",
                "resources/js/farmasi/pengambilan-obat/data-pengambilan-obat-sudah-selesai.js",
                "resources/js/farmasi/cetak-resep-obat/data-cetak-resep-obat.js",
            ],
            refresh: true,
        }),
    ],
});
