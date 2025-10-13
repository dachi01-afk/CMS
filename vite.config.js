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

                // poli
                "resources/js/admin/poli/data-poli.js",

                // layanan
                "resources/js/admin/poli/data-poli.js",

                // manajemen-pengguna
                "resources/js/admin/manajemenPengguna/data_pengguna.js",
                "resources/js/admin/manajemenPengguna/data_dokter.js",
                "resources/js/admin/manajemenPengguna/data_apoteker.js",
                "resources/js/admin/manajemenPengguna/data_pasien.js",
                "resources/js/admin/pengambilanObat/pengambilan_obat.js",
                "resources/js/admin/jadwalKunjungan/jadwal_kunjungan.js",
                "resources/js/admin/jadwalKunjungan/proses_kunjungan.js",



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

                // transaksi 
                "resources/js/admin/pengambilanObat/transaksi-menunggu.js"
            ],
            refresh: true,
        }),
    ],
});
