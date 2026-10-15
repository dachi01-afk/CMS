<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Field yang dikecualikan dari old_values / new_values
    |--------------------------------------------------------------------------
    |
    | Audit log TIDAK BOLEH menyimpan kredensial atau data rahasia. Field yang
    | namanya sama, berawalan, atau berakhiran salah satu kata kunci di bawah
    | akan dibuang sebelum log ditulis.
    |
    */
    'excluded_fields' => [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'remember_token',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        'fcm_token',
        'otp',
        'otp_code',
        'kode_otp',
        'pin',
        'secret',
        'client_secret',
        'private_key',
        'signature',
        'authorization',
    ],

    /*
    |--------------------------------------------------------------------------
    | Jam & hari operasional klinik
    |--------------------------------------------------------------------------
    |
    | Dipakai dashboard auditor untuk menyorot aktivitas di luar jam operasional.
    | Hari memakai format ISO-8601 (1 = Senin ... 7 = Minggu).
    |
    */
    'operational_hours' => [
        'start' => '07:00',
        'end' => '21:00',
    ],

    'operational_days' => [1, 2, 3, 4, 5, 6],

    /*
    |--------------------------------------------------------------------------
    | Pemetaan route → [action, module]
    |--------------------------------------------------------------------------
    |
    | Aksi yang tidak bisa ditangkap model observer (membaca detail, export,
    | cetak, perubahan pengaturan) dicatat otomatis oleh middleware
    | RecordAuditableRequest. Pola mendukung wildcard gaya Str::is() dan
    | diperiksa berurutan dari atas ke bawah.
    |
    */
    'route_actions' => [

        // ---- Aktivitas auditor sendiri ----
        'auditor.dashboard' => ['view', 'Audit Log'],
        'auditor.logs.index' => ['view', 'Audit Log'],
        'auditor.logs.data' => ['view', 'Audit Log'],
        'auditor.logs.show' => ['view', 'Audit Log'],
        'auditor.logs.entity' => ['view', 'Audit Log'],
        'auditor.logs.export' => ['export', 'Audit Log'],

        // ---- LIHAT detail pasien ----
        'manajemen_pengguna.show.detail.pasien' => ['view', 'Pasien'],
        'manajemen_pengguna.get_pasien_by_id' => ['view', 'Pasien'],
        'admin.detail.pasien.hari.ini' => ['view', 'Pasien'],
        'super.admin.detail.pasien.hari.ini' => ['view', 'Pasien'],
        'super.admin.pasien.insight.show' => ['view', 'Pasien'],

        // ---- LIHAT rekam medis (akses baca wajib dicatat) ----
        'data_medis_pasien.index' => ['view', 'Rekam Medis'],
        'data_medis_pasien.laporan_rekam_medis' => ['view', 'Rekam Medis'],
        'data_medis_pasien.detail_emr' => ['view', 'Rekam Medis'],
        'data_medis_pasien.detail.emr.pasien' => ['view', 'Rekam Medis'],
        'data_medis_pasien.get.data.emr.by.id' => ['view', 'Rekam Medis'],
        'riwayat-pemeriksaan.emr.show' => ['view', 'Rekam Medis'],
        'riwayat-pemeriksaan.lab.show' => ['view', 'Hasil Lab'],
        'riwayat-pemeriksaan.radiologi.show' => ['view', 'Hasil Radiologi'],

        // ---- LIHAT resep ----
        'pengambilan.obat.get.data.resep.obat.by.id' => ['view', 'Resep'],
        'pengambilan.obat.get.data.resep.obat.detail' => ['view', 'Resep'],
        'get.data.detail.order.obat' => ['view', 'Resep'],

        // ---- Cetak dokumen ----
        'manajemen_pengguna.cetak.stiker.pasien' => ['print', 'Pasien'],
        'kasir.show.kwitansi' => ['print', 'Pembayaran'],
        'get.show.kwitansi.transaksi.obat' => ['print', 'Pembayaran'],
        'kasir.show.kwitansi.transaksi.layanan' => ['print', 'Pembayaran'],
        'cetak.resep.obat.print.preview' => ['print', 'Cetak Resep'],
        'print.data.obat' => ['print', 'Obat'],
        'print.data.penggunaan.obat' => ['print', 'Penggunaan Obat'],
        'print.pdf.data.bahan.habis.pakai' => ['print', 'Bahan Habis Pakai'],
        'print.pdf.data.penggunaan.bhp' => ['print', 'Penggunaan BHP'],
        'super.admin.report.kunjungan.pdf' => ['print', 'Laporan Kunjungan'],

        // ---- Export data ----
        'manajemen_pengguna.export_pasien_excel' => ['export', 'Pasien'],
        'layanan.export.excel' => ['export', 'Layanan'],
        'laporan.export' => ['export', 'Laporan Kunjungan'],
        'laporan.export.laporan.keuangan' => ['export', 'Laporan Keuangan'],
        'export.data.obat' => ['export', 'Obat'],
        'export.data.penggunaan.obat' => ['export', 'Penggunaan Obat'],
        'export.excel.data.bahan.habis.pakai' => ['export', 'Bahan Habis Pakai'],
        'export.data.penggunaan.bhp' => ['export', 'Penggunaan BHP'],
        'super.admin.report.kunjungan.excel' => ['export', 'Laporan Kunjungan'],

        // ---- Perubahan pengaturan sistem ----
        'settings.profile.update' => ['update', 'Pengaturan Sistem'],
        'settings.password.update' => ['update', 'Pengaturan Sistem'],
        'password.update' => ['update', 'Pengaturan Sistem'],
        'pengaturan_klinik.add_jadwal_dokter' => ['create', 'Pengaturan Klinik'],
        'pengaturan_klinik.update_jadwal_dokter' => ['update', 'Pengaturan Klinik'],
        'pengaturan_klinik.delete_jadwal_dokter' => ['delete', 'Pengaturan Klinik'],

        // ---- Pembayaran: batal / diskon ----
        'kasir.pembayaran.delete' => ['cancel', 'Pembayaran'],
        'kasir.transaksi.item.delete' => ['update', 'Pembayaran'],
        'kasir.transaksi.obat.diskon.request' => ['update', 'Pembayaran'],
        'kasir.request.diskon.order.layanan' => ['update', 'Pembayaran'],
        'super.admin.diskon.approve' => ['update', 'Pembayaran'],
        'super.admin.diskon.reject' => ['update', 'Pembayaran'],
        'super.admin.approve.diskon.penjualan.obat.approve' => ['update', 'Pembayaran'],
        'super.admin.reject.diskon.penjualan.obat' => ['update', 'Pembayaran'],
        'super.admin.approve.diskon.order.layanan' => ['update', 'Pembayaran'],
        'super.admin.reject.diskon.order.layanan' => ['update', 'Pembayaran'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route → entitas yang diakses
    |--------------------------------------------------------------------------
    |
    | Dipakai agar log "LIHAT" bisa ditautkan ke entitasnya, sehingga halaman
    | riwayat per pasien/rekam medis menampilkan siapa saja yang mengaksesnya.
    |
    | Format: nama-route => [kelas model, nama parameter route, kolom pencarian]
    |
    */
    'route_entities' => [
        'manajemen_pengguna.show.detail.pasien' => [App\Models\Pasien::class, 'noEMR', 'no_emr'],
        'manajemen_pengguna.get_pasien_by_id' => [App\Models\Pasien::class, 'id', 'id'],
        'admin.detail.pasien.hari.ini' => [App\Models\Pasien::class, 'no_emr', 'no_emr'],
        'super.admin.detail.pasien.hari.ini' => [App\Models\Pasien::class, 'no_emr', 'no_emr'],
        'super.admin.pasien.insight.show' => [App\Models\Pasien::class, 'id', 'id'],
        'data_medis_pasien.detail_emr' => [App\Models\Pasien::class, 'no_emr', 'no_emr'],
        'data_medis_pasien.detail.emr.pasien' => [App\Models\Pasien::class, 'noEMR', 'no_emr'],
        'data_medis_pasien.get.data.emr.by.id' => [App\Models\EMR::class, 'id', 'id'],
        'pengambilan.obat.get.data.resep.obat.by.id' => [App\Models\Resep::class, 'id', 'id'],
        'pengambilan.obat.get.data.resep.obat.detail' => [App\Models\Resep::class, 'id', 'id'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Method tulis yang tetap diizinkan untuk Auditor
    |--------------------------------------------------------------------------
    |
    | Auditor diblokir dari seluruh request non-GET kecuali nama route di bawah
    | ini (logout & konfirmasi password tidak mengubah data klinis).
    |
    */
    'auditor_allowed_write_routes' => [
        'logout',
        'password.confirm',
    ],

    /*
    |--------------------------------------------------------------------------
    | Modul untuk filter di halaman auditor
    |--------------------------------------------------------------------------
    */
    'modules' => [
        'Pasien',
        'Rekam Medis',
        'Resep',
        'Obat',
        'Stok Obat',
        'Pembayaran',
        'Farmasi',
        'Penggunaan Obat',
        'Penggunaan BHP',
        'Bahan Habis Pakai',
        'Layanan',
        'Cetak Resep',
        'Manajemen User',
        'Pengaturan Sistem',
        'Pengaturan Klinik',
        'Laporan Kunjungan',
        'Laporan Keuangan',
        'Hasil Lab',
        'Hasil Radiologi',
        'Autentikasi',
        'Audit Log',
    ],
];
