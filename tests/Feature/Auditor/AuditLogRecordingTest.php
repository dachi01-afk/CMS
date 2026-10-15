<?php

use App\Models\AuditLog;
use App\Models\EMR;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Poli;
use App\Models\User;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Str;

/**
 * Memastikan pencatatan berjalan otomatis lewat observer, event, dan middleware.
 */

test('perubahan pasien dicatat lengkap dengan diff nilai lama dan baru', function () {
    $admin = userKlinik('Admin');
    $this->actingAs($admin);

    $pasienUser = userKlinik('Pasien');
    $pasien = pasienKlinik($pasienUser, ['nama_pasien' => 'Budi Santoso']);

    // ---- create ----
    $createLog = AuditLog::where('action', 'create')->where('module', 'Pasien')->latest('id')->first();

    expect($createLog)->not->toBeNull()
        ->and($createLog->auditable_type)->toBe(Pasien::class)
        ->and($createLog->auditable_id)->toBe($pasien->id)
        ->and($createLog->user_id)->toBe($admin->id)
        ->and($createLog->user_name)->toBe($admin->username)
        ->and($createLog->user_role)->toBe('Admin')
        ->and($createLog->new_values)->toHaveKey('nama_pasien')
        ->and($createLog->description)->toContain('Menambah data Pasien');

    // ---- update: hanya field yang berubah yang dicatat ----
    $pasien->update(['nama_pasien' => 'Budi Santoso Jaya', 'alamat' => 'Jl. Melati 1']);

    $updateLog = AuditLog::where('action', 'update')->where('module', 'Pasien')->latest('id')->first();

    expect($updateLog)->not->toBeNull()
        ->and($updateLog->old_values)->toHaveKey('nama_pasien')
        ->and($updateLog->old_values['nama_pasien'])->toBe('Budi Santoso')
        ->and($updateLog->new_values['nama_pasien'])->toBe('Budi Santoso Jaya')
        ->and($updateLog->new_values)->toHaveKey('alamat')
        ->and(array_keys($updateLog->new_values))->not->toContain('updated_at')
        ->and($updateLog->description)->toContain('Field berubah');

    // ---- delete ----
    $pasien->delete();

    $deleteLog = AuditLog::where('action', 'delete')->where('module', 'Pasien')->latest('id')->first();

    expect($deleteLog)->not->toBeNull()
        ->and($deleteLog->auditable_id)->toBe($pasien->id)
        ->and($deleteLog->old_values)->toHaveKey('nama_pasien');
});

test('update yang tidak mengubah field tidak menghasilkan log', function () {
    $admin = userKlinik('Admin');
    $this->actingAs($admin);

    $pasien = pasienKlinik(userKlinik('Pasien'));

    $before = AuditLog::where('action', 'update')->where('auditable_type', Pasien::class)->count();

    $pasien->save(); // tidak ada perubahan berarti

    $after = AuditLog::where('action', 'update')->where('auditable_type', Pasien::class)->count();

    expect($after)->toBe($before);
});

test('login berhasil, login gagal, dan logout tercatat', function () {
    $user = userKlinik('Admin');

    // login gagal
    $this->post('/login', ['login' => $user->email, 'password' => 'password-salah']);
    $this->assertGuest();

    expect(AuditLog::where('action', 'login_failed')->exists())->toBeTrue();

    // login berhasil
    $this->post('/login', ['login' => $user->email, 'password' => 'Password123!']);

    $loginLog = AuditLog::where('action', 'login')->latest('id')->first();
    expect($loginLog)->not->toBeNull()
        ->and($loginLog->user_id)->toBe($user->id)
        ->and($loginLog->module)->toBe('Autentikasi')
        ->and($loginLog->user_role)->toBe('Admin');

    // logout
    $this->post('/logout');
    $this->assertGuest();

    expect(AuditLog::where('action', 'logout')->exists())->toBeTrue();
});

test('akses baca (LIHAT) detail pasien tercatat dan tertaut ke entitasnya', function () {
    $admin = userKlinik('Admin');
    $pasien = pasienKlinik(userKlinik('Pasien'));

    $this->actingAs($admin)
        ->get(route('manajemen_pengguna.get_pasien_by_id', $pasien->id))
        ->assertOk();

    $viewLog = AuditLog::where('action', 'view')->where('module', 'Pasien')->latest('id')->first();

    expect($viewLog)->not->toBeNull()
        ->and($viewLog->user_id)->toBe($admin->id)
        ->and($viewLog->auditable_type)->toBe(Pasien::class)
        ->and($viewLog->auditable_id)->toBe($pasien->id);
});

test('akses baca rekam medis tercatat', function () {
    $admin = userKlinik('Admin');
    $pasien = pasienKlinik(userKlinik('Pasien'));

    $poli = Poli::create(['nama_poli' => 'Poli Uji '.Str::random(4)]);

    $kunjungan = Kunjungan::create([
        'poli_id' => $poli->id,
        'pasien_id' => $pasien->id,
        'tanggal_kunjungan' => now()->toDateString(),
    ]);

    $emr = EMR::create([
        'kunjungan_id' => $kunjungan->id,
        'pasien_id' => $pasien->id,
        'poli_id' => $poli->id,
    ]);

    $this->actingAs($admin)
        ->get(route('data_medis_pasien.get.data.emr.by.id', $emr->id))
        ->assertOk();

    $viewLog = AuditLog::where('action', 'view')->where('module', 'Rekam Medis')->latest('id')->first();

    expect($viewLog)->not->toBeNull()
        ->and($viewLog->auditable_type)->toBe(EMR::class)
        ->and($viewLog->auditable_id)->toBe($emr->id);
});

test('aktivitas auditor sendiri (membuka dan mengekspor log) tercatat', function () {
    $auditor = userKlinik('Auditor');

    $this->actingAs($auditor)->get(route('auditor.logs.index'))->assertOk();
    $this->actingAs($auditor)->get(route('auditor.logs.export', ['format' => 'csv']))->assertOk();

    $viewLog = AuditLog::where('action', 'view')
        ->where('module', 'Audit Log')
        ->where('user_id', $auditor->id)
        ->latest('id')
        ->first();

    $exportLog = AuditLog::where('action', 'export')
        ->where('module', 'Audit Log')
        ->where('user_id', $auditor->id)
        ->latest('id')
        ->first();

    expect($viewLog)->not->toBeNull()
        ->and($exportLog)->not->toBeNull()
        ->and($exportLog->user_role)->toBe('Auditor');
});

test('audit log bersifat append-only untuk semua role', function () {
    $admin = userKlinik('Admin');
    $this->actingAs($admin);

    $log = AuditLog::create([
        'user_id' => $admin->id,
        'user_name' => $admin->username,
        'user_role' => $admin->role,
        'action' => 'create',
        'module' => 'Pasien',
        'description' => 'Log uji append-only.',
    ]);

    expect(fn () => $log->update(['module' => 'DIUBAH']))->toThrow(LogicException::class);
    expect(fn () => $log->delete())->toThrow(LogicException::class);
    expect(fn () => AuditLog::query()->where('id', $log->id)->update(['module' => 'DIUBAH']))
        ->toThrow(LogicException::class);
    expect(fn () => AuditLog::query()->where('id', $log->id)->delete())->toThrow(LogicException::class);

    // Log tetap utuh.
    $masihAda = AuditLog::find($log->id);
    expect($masihAda)->not->toBeNull()
        ->and($masihAda->module)->toBe('Pasien');
});

test('kegagalan menulis log ditelan dan tidak menggagalkan proses utama', function () {
    $admin = userKlinik('Admin');
    $this->actingAs($admin);

    $sebelum = AuditLog::count();

    // Kolom `module` adalah varchar(100); nilai 200 karakter membuat INSERT
    // gagal di MySQL sehingga jalur kegagalan AuditLogger benar-benar teruji.
    $hasil = AuditLogger::record('create', str_repeat('X', 200), 'Uji ketahanan logger.');

    expect($hasil)->toBeNull();
    expect(AuditLog::count())->toBe($sebelum);

    // Proses utama tetap berjalan normal setelah kegagalan tersebut.
    expect(AuditLogger::record('create', 'Pasien', 'Log normal setelah kegagalan.'))->not->toBeNull();
});

test('heartbeat tidak membanjiri audit log dengan perubahan terakhir_login', function () {
    $admin = userKlinik('Admin');
    $this->actingAs($admin);

    $sebelum = AuditLog::count();

    // Heartbeat jalan tiap 60 detik dan memperbarui kolom terakhir_login.
    $this->post('/heartbeat')->assertOk();

    expect(AuditLog::count())->toBe($sebelum)
        ->and($admin->fresh()->terakhir_login)->not->toBeNull();

    // Perubahan user yang sungguhan tetap dicatat.
    $admin->update(['username' => 'admin_berubah_'.$admin->id]);

    $log = AuditLog::where('action', 'update')->where('module', 'Manajemen User')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->new_values)->toHaveKey('username')
        ->and($log->new_values)->not->toHaveKey('terakhir_login');
});

test('reset password lewat fitur lupa password tercatat tanpa isi password', function () {
    $user = userKlinik('Admin');

    Illuminate\Support\Facades\Notification::fake();

    $this->post('/forgot-password', ['email' => $user->email]);

    Illuminate\Support\Facades\Notification::assertSentTo(
        $user,
        Illuminate\Auth\Notifications\ResetPassword::class,
        function ($notification) use ($user) {
            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'PasswordBaru999!',
                'password_confirmation' => 'PasswordBaru999!',
            ]);

            return true;
        }
    );

    $log = AuditLog::where('action', 'reset_password')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($user->id)
        ->and($log->module)->toBe('Autentikasi')
        ->and($log->old_values)->toBeNull()
        ->and($log->new_values)->toBeNull();
});

test('perubahan stok obat lewat model depot dan batch ikut tercatat', function () {
    $admin = userKlinik('Admin');
    $this->actingAs($admin);

    $obat = App\Models\Obat::create([
        'nama_obat' => 'Obat Uji '.Str::random(4),
        'jumlah' => 100,
        'dosis' => 1,
        'total_harga' => 5000,
    ]);

    $depotObat = App\Models\DepotObat::create([
        'obat_id' => $obat->id,
        'depot_id' => App\Models\Depot::create(['nama_depot' => 'Depot Uji '.Str::random(4)])->id,
        'stok_obat' => 10,
    ]);

    $logCreate = AuditLog::where('module', 'Stok Obat')->where('action', 'create')->latest('id')->first();

    expect($logCreate)->not->toBeNull()
        ->and($logCreate->auditable_type)->toBe(App\Models\DepotObat::class)
        ->and($logCreate->new_values['stok_obat'])->toBe(10);

    // Perubahan jumlah stok dicatat sebagai update dengan diff lama vs baru.
    $depotObat->update(['stok_obat' => 25]);

    $logUpdate = AuditLog::where('module', 'Stok Obat')->where('action', 'update')->latest('id')->first();

    expect($logUpdate)->not->toBeNull()
        ->and($logUpdate->old_values['stok_obat'])->toBe(10)
        ->and($logUpdate->new_values['stok_obat'])->toBe(25)
        ->and($logUpdate->description)->toContain('Mengubah data Stok Obat');
});

test('modul pembayaran dan stok obat ikut tercatat otomatis', function () {
    $admin = userKlinik('Admin');
    $this->actingAs($admin);

    $pasien = pasienKlinik(userKlinik('Pasien'));

    App\Models\PenjualanObat::create([
        'pasien_id' => $pasien->id,
        'kode_transaksi' => 'TRX-'.Str::upper(Str::random(6)),
        'total_tagihan' => 50000,
    ]);

    $log = AuditLog::where('module', 'Pembayaran')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->action)->toBe('create');
});
