<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cara menangkap perubahan data
    |--------------------------------------------------------------------------
    |
    | 'trigger'  : (default) setiap INSERT/UPDATE/DELETE di SEMUA tabel dicatat
    |              oleh trigger MySQL. Ini menangkap semua jalur penulisan:
    |              Eloquent, DB::table() / raw query, update/delete massal,
    |              API mobile, webhook, sampai fitur baru yang belum ada.
    | 'observer' : cadangan bila server database tidak mengizinkan pembuatan
    |              trigger. Hanya model yang memakai trait Auditable yang
    |              tercatat, dan raw query TIDAK tertangkap.
    |
    | Trigger dibuat otomatis setiap kali `php artisan migrate` selesai, dan
    | bisa dibuat ulang manual dengan `php artisan audit:triggers`.
    |
    */
    'capture' => env('AUDIT_CAPTURE', 'trigger'),

    /*
    |--------------------------------------------------------------------------
    | Tabel yang TIDAK dipasangi trigger
    |--------------------------------------------------------------------------
    |
    | Hanya tabel teknis framework (antrian, cache, sesi, token login) dan
    | notifikasi otomatis. Semua tabel data klinik tetap tercatat. Pola
    | mendukung wildcard gaya Str::is().
    |
    */
    'excluded_tables' => [
        'audit_logs',
        'migrations',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'sessions',
        'password_reset_tokens',
        'password_resets',
        'personal_access_tokens',
        'notifications',
        'notification',
        'telescope_*',
        'pulse_*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Kolom yang diabaikan saat mendeteksi perubahan
    |--------------------------------------------------------------------------
    |
    | Kolom teknis yang berubah otomatis. Bila sebuah UPDATE hanya mengubah
    | kolom-kolom ini, tidak ada log yang ditulis (mis. heartbeat yang
    | memperbarui terakhir_login setiap 60 detik).
    |
    */
    'ignored_columns' => [
        'updated_at',
        'remember_token',
        'terakhir_login',
    ],

    /*
    |--------------------------------------------------------------------------
    | Nama modul per tabel
    |--------------------------------------------------------------------------
    |
    | Dipakai kolom "Modul" di audit log. Tabel yang tidak ada di daftar ini
    | memakai nama tabelnya (mis. order_lab_detail → "Order Lab Detail").
    |
    */
    'table_modules' => [
        // Pasien & kunjungan
        'pasien' => 'Pasien',
        'testimoni' => 'Testimoni',
        'kunjungan' => 'Kunjungan',
        'kunjungan_layanan' => 'Kunjungan',
        'jadwal_dokter' => 'Pengaturan Klinik',

        // Rekam medis
        'emr' => 'Rekam Medis',
        'emr_kklp*' => 'Rekam Medis',
        'emr_pengkajian_awal_penyakit_dalam*' => 'Rekam Medis',
        'resume_dokter' => 'Rekam Medis',
        'dental_*' => 'Rekam Medis',
        'vital_signs' => 'Rekam Medis',
        'psikososial_spiritual' => 'Rekam Medis',
        'tes_lab' => 'Rekam Medis',

        // Laboratorium & radiologi
        'order_lab*' => 'Laboratorium',
        'hasil_lab' => 'Hasil Lab',
        'jenis_pemeriksaan_lab' => 'Laboratorium',
        'satuan_lab' => 'Laboratorium',
        'order_radiologi*' => 'Radiologi',
        'hasil_radiologi' => 'Hasil Radiologi',
        'jenis_pemeriksaan_radiologi' => 'Radiologi',

        // Resep & obat
        'resep' => 'Resep',
        'resep_obat' => 'Resep',
        'obat' => 'Obat',
        'kategori_obat' => 'Data Master Farmasi',
        'jenis_obat' => 'Data Master Farmasi',
        'satuan_obat' => 'Data Master Farmasi',
        'brand_farmasi' => 'Data Master Farmasi',
        'supplier' => 'Data Master Farmasi',
        'depot' => 'Data Master Farmasi',
        'tipe_depot' => 'Data Master Farmasi',
        'batch_obat*' => 'Stok Obat',
        'depot_obat' => 'Stok Obat',
        'mutasi_stok_obat*' => 'Stok Obat',
        'restock_obat*' => 'Stok Obat',
        'return_obat*' => 'Stok Obat',
        'stok_transaksi*' => 'Stok Obat',

        // Bahan habis pakai
        'bahan_habis_pakai' => 'Bahan Habis Pakai',
        'batch_bahan_habis_pakai*' => 'Bahan Habis Pakai',
        'depot_bhp' => 'Bahan Habis Pakai',
        'restock_bahan_habis_pakai*' => 'Bahan Habis Pakai',
        'return_bahan_habis_pakai*' => 'Bahan Habis Pakai',
        'riwayat_penggunaan_bahan_habis_pakai' => 'Penggunaan BHP',

        // Keuangan
        'pembayaran' => 'Pembayaran',
        'pembayaran_detail' => 'Pembayaran',
        'penjualan_obat' => 'Pembayaran',
        'penjualan_obat_detail' => 'Pembayaran',
        'penjualan_layanan' => 'Pembayaran',
        'order_layanan' => 'Order Layanan',
        'order_layanan_detail' => 'Order Layanan',
        'administrasi' => 'Pembayaran',
        'metode_pembayaran' => 'Pengaturan Kasir',
        'diskon_approval' => 'Approval Diskon',
        'approve_diskon_*' => 'Approval Diskon',
        'hutang_*' => 'Hutang & Piutang',
        'piutang_*' => 'Hutang & Piutang',

        // Layanan & poli
        'layanan' => 'Layanan',
        'layanan_poli' => 'Layanan',
        'kategori_layanan' => 'Layanan',
        'poli' => 'Pengaturan Klinik',
        'jenis_spesialis' => 'Pengaturan Klinik',
        'dokter_poli' => 'Pengaturan Klinik',
        'perawat_dokter_poli' => 'Pengaturan Klinik',

        // Pengguna
        'user' => 'Manajemen User',
        'users' => 'Manajemen User',
        'admin' => 'Manajemen User',
        'super_admin' => 'Manajemen User',
        'dokter' => 'Manajemen User',
        'perawat' => 'Manajemen User',
        'farmasi' => 'Manajemen User',
        'kasir' => 'Manajemen User',
        'auditor' => 'Manajemen User',
    ],

    /*
    | Nama entitas di kalimat deskripsi ("Menambah data <nama>"). Tabel lain
    | memakai nama tabelnya, mis. hasil_lab → "Hasil Lab".
    */
    'table_entities' => [
        'user' => 'User',
        'emr' => 'Rekam Medis (EMR)',
        'emr_kklp' => 'EMR KKLP',
        'tes_lab' => 'Tes Lab',
        'bahan_habis_pakai' => 'Bahan Habis Pakai',
        'depot_bhp' => 'Stok BHP per Depot',
        'batch_obat_depot' => 'Stok Batch Obat per Depot',
        'depot_obat' => 'Stok Obat per Depot',
    ],

    /*
    |--------------------------------------------------------------------------
    | Kolom label entitas
    |--------------------------------------------------------------------------
    |
    | Kolom pertama yang ada di tabel dipakai sebagai keterangan di deskripsi,
    | mis. "Mengubah data Pasien (Budi Santoso)". Bila tidak ada, dipakai kolom
    | pertama yang berawalan nama_ / kode_ / no_, lalu ID baris.
    |
    */
    'label_columns' => [
        'nama_pasien',
        'nama_obat',
        'nama_auditor',
        'nama_dokter',
        'nama_perawat',
        'nama_farmasi',
        'nama_kasir',
        'nama_admin',
        'nama_super_admin',
        'no_emr',
        'kode_transaksi',
        'username',
        'nama_layanan',
        'nama_supplier',
        'nama_depot',
        'no_faktur',
        'kode_resep',
        'nama_batch',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pencatatan akses (membuka halaman / data)
    |--------------------------------------------------------------------------
    |
    | Selain route yang dipetakan di 'route_actions', middleware mencatat:
    |  - web : setiap halaman yang dibuka, dan setiap permintaan AJAX yang
    |         mengambil SATU data tertentu (route dengan parameter, mis. detail
    |         pasien). Permintaan AJAX pengisi tabel/grafik/dropdown tanpa
    |         parameter dianggap bagian dari halaman yang sedang dibuka.
    |  - api : setiap permintaan baca dari aplikasi mobile.
    |  - semua: setiap akses yang DITOLAK (HTTP 403).
    |
    | Route/URL di bawah ini tidak dicatat karena dikirim otomatis oleh
    | browser/aplikasi, bukan aktivitas pengguna.
    |
    */
    'ignored_routes' => [
        'heartbeat',
    ],

    'ignored_paths' => [
        'heartbeat',
        'up',
        'sanctum/csrf-cookie',
        'livewire/*',
        '_debugbar/*',
    ],

    /*
    | Segmen URL yang hanya menandakan role, sehingga nama modul diambil dari
    | segmen sesudahnya (mis. /farmasi/restock-obat → "Restock Obat").
    */
    'role_path_segments' => [
        'api', 'admin', 'super-admin', 'farmasi', 'kasir', 'perawat', 'dokter', 'pasien', 'auditor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autentikasi lewat API mobile
    |--------------------------------------------------------------------------
    |
    | Login aplikasi mobile tidak memakai event Laravel (memakai Hash::check
    | manual), jadi dicatat dari hasil respons route-nya. 'identity' adalah
    | field request yang dipakai untuk mencari user (password tidak pernah
    | dibaca).
    |
    */
    'api_auth_routes' => [
        'api.login' => ['identity' => 'username', 'success' => 'login', 'failed' => 'login_failed', 'label' => 'Login aplikasi mobile'],
        'api.login_dokter' => ['identity' => 'username', 'success' => 'login', 'failed' => 'login_failed', 'label' => 'Login aplikasi mobile dokter'],
        'api.logout' => ['identity' => null, 'success' => 'logout', 'failed' => null, 'label' => 'Logout aplikasi mobile'],
        'api.register' => ['identity' => 'username', 'success' => 'register', 'failed' => 'register_failed', 'label' => 'Registrasi akun pasien'],
        'forgot_password.send_otp' => ['identity' => 'email', 'success' => 'request_otp', 'failed' => 'request_otp_failed', 'label' => 'Permintaan OTP lupa password'],
        'forgot_password.reset' => ['identity' => 'email', 'success' => 'reset_password', 'failed' => 'reset_password_failed', 'label' => 'Reset password dengan OTP'],
        'forgot_username.send_otp' => ['identity' => 'email', 'success' => 'request_otp', 'failed' => 'request_otp_failed', 'label' => 'Permintaan OTP lupa username'],
        'forgot_username.verify_or_change' => ['identity' => 'email', 'success' => 'change_username', 'failed' => 'change_username_failed', 'label' => 'Verifikasi / ganti username dengan OTP'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Parameter route → pasien
    |--------------------------------------------------------------------------
    |
    | Supaya log akses ("membuka data") bisa ditautkan ke pasiennya dan muncul
    | di halaman Riwayat per Entitas. Format: nama-parameter => [tabel, kolom].
    | Nama parameter dibandingkan tanpa huruf besar/kecil dan tanpa "_".
    |
    */
    'route_parameter_tables' => [
        'noemr' => ['pasien', 'no_emr'],
        'pasienid' => ['pasien', 'id'],
        'pasien' => ['pasien', 'id'],
        'kunjunganid' => ['kunjungan', 'id'],
        'kunjungan' => ['kunjungan', 'id'],
        'emrid' => ['emr', 'id'],
        'emr' => ['emr', 'id'],
        'orderlabid' => ['order_lab', 'id'],
        'orderradiologiid' => ['order_radiologi', 'id'],
        'kklpid' => ['emr_kklp', 'id'],
    ],

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

        // ---- Import data ----
        'import.data.obat' => ['import', 'Obat'],
        'import.excel.data.bahan.habis.pakai' => ['import', 'Bahan Habis Pakai'],

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
