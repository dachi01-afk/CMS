import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",

                "resources/js/super-admin/data-dashboard-super-admin.js",

                // Super Admin (Manager) Approval Diskon
                "resources/js/super-admin/data-belum-approve-diskon.js",
                "resources/js/super-admin/data-sudah-approve-diskon.js",

                // Approve Diskon Penjualan Obat
                "resources/js/super-admin/approve-diskon-penjualan-obat/data-belum-approve-diskon-penjualan-obat.js",
                "resources/js/super-admin/approve-diskon-penjualan-obat/data-sudah-approve-diskon-penjualan-obat.js",

                // Approve Diskon Order Layanan
                "resources/js/super-admin/approve-diskon-order-layanan/data-belum-approve-diskon-order-layanan.js",
                "resources/js/super-admin/approve-diskon-order-layanan/data-sudah-approve-diskon-order-layanan.js",

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
                "resources/js/admin/manajemenPengguna/data_admin.js",
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

                // Insight Transaksi (Alur Default, Transaksi Obat, Transaksi Layanan)
                "resources/js/kasir/insight-transaksi.js",

                // Hutang
                "resources/js/kasir/hutang/data-hutang.js",

                // Riwayat Hutang
                "resources/js/kasir/hutang/data-riwayat-hutang.js",

                // Hutang bahan habis pakai
                "resources/js/kasir/hutang-bahan-habis-pakai/data-hutang-bahan-habis-pakai.js",

                // Riwayat Hutang bahan habis pakai
                "resources/js/kasir/hutang-bahan-habis-pakai/data-riwayat-hutang-bahan-habis-pakai.js",

                // Piutang Obat
                "resources/js/kasir/piutang-obat/data-piutang-obat.js",

                // Riwayat Piutang Obat
                "resources/js/kasir/piutang-obat/data-riwayat-piutang-obat.js",

                // Piutang Bahan Habis Pakai
                "resources/js/kasir/piutang-bahan-habis-pakai/data-piutang-bahan-habis-pakai.js",

                // Riwayat Piutang Bahan Habis Pakai
                "resources/js/kasir/piutang-bahan-habis-pakai/data-riwayat-piutang-bahan-habis-pakai.js",

                // End Role Kasir

                // Role Perawat
                "resources/js/perawat/dashboard.js",
                "resources/js/perawat/kunjungan/data-kunjungan-hari-ini.js",
                "resources/js/perawat/kunjungan/data-triage-pasien.js",
                "resources/js/perawat/kunjungan/form-pengisian-vital-sign-pasien.js",
                "resources/js/perawat/kunjungan/data-tes-laboratorium.js",
                "resources/js/perawat/kunjungan/data-tes-radiologi.js",

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
                "resources/js/farmasi/restock-dan-return-obat-dan-bhp/data-restock-dan-return-obat-dan-bhp.js",
                "resources/js/farmasi/depot/data-depot.js",
                "resources/js/farmasi/penggunaan-bhp/data-penggunaan-bhp.js",
                "resources/js/farmasi/bahan-habis-pakai/data-pemakaian-bhp.js",
                "resources/js/farmasi/pesanan-dan-stok-masuk/data-pesanan-dan-stok-masuk.js",

                // Restock Obat
                "resources/js/farmasi/restock-obat/data-restock-obat.js",

                // Riwayat Restock Obat
                "resources/js/farmasi/restock-obat/data-riwayat-restock-obat.js",

                // Stok Masuk Obat
                "resources/js/farmasi/stok-masuk-obat/data-stok-masuk-obat.js",

                // Riwayat Stok Masuk Obat
                "resources/js/farmasi/stok-masuk-obat/data-riwayat-stok-masuk-obat.js",

                // Restock Bahan Habis Pakai
                "resources/js/farmasi/restock-bahan-habis-pakai/data-restock-bahan-habis-pakai.js",

                // Riwayat Restock Bahan Habis Pakai
                "resources/js/farmasi/restock-bahan-habis-pakai/data-riwayat-restock-bahan-habis-pakai.js",

                // Stok Masuk Bahan Habis Pakai
                "resources/js/farmasi/stok-masuk-bahan-habis-pakai/data-stok-masuk-bahan-habis-pakai.js",

                // Riwayat Stok Masuk Bahan Habis Pakai
                "resources/js/farmasi/stok-masuk-bahan-habis-pakai/data-riwayat-stok-masuk-bahan-habis-pakai.js",

                // Return Obat
                "resources/js/farmasi/return-obat/data-return-obat.js",

                // Return Bahan Habis Pakai
                "resources/js/farmasi/return-bahan-habis-pakai/data-return-bahan-habis-pakai.js",

                // riwayat pemeriksaaan
                "resources/js/perawat/riwayat-pemeriksaan/emr.js",
                "resources/js/perawat/riwayat-pemeriksaan/data-lab.js",
                "resources/js/perawat/riwayat-pemeriksaan/data-radiologi.js",
            ],
            refresh: true,
        }),
    ],
});
