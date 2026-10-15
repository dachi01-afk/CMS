<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Memastikan password, token, dan field rahasia lain tidak pernah tersimpan
 * di old_values / new_values.
 */

test('sanitizer membuang seluruh field rahasia dan menyisakan field biasa', function () {
    $bersih = AuditLogger::sanitize([
        'nama_pasien' => 'Budi',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'current_password' => 'lama',
        'new_password' => 'baru',
        'remember_token' => 'remember-abc',
        'fcm_token' => 'fcm-abc',
        'api_token' => 'api-abc',
        'otp_code' => '123456',
        'kode_otp' => '654321',
        'pin' => '4321',
        'secret' => 'sssh',
        'authorization' => 'Bearer xyz',
        'nik' => '3171010101010001',
    ]);

    expect($bersih)->toBe([
        'nama_pasien' => 'Budi',
        'nik' => '3171010101010001',
    ]);
});

test('pembuatan user tidak menyimpan password dan token di audit log', function () {
    $admin = userKlinik('Admin');
    $this->actingAs($admin);

    $user = User::create([
        'username' => 'pengguna_rahasia',
        'email' => 'pengguna_rahasia@example.test',
        'password' => 'SangatRahasia123!',
        'role' => 'Kasir',
        'fcm_token' => 'token-fcm-yang-tidak-boleh-tersimpan',
        'remember_token' => 'remember-yang-tidak-boleh-tersimpan',
    ]);

    $log = AuditLog::where('action', 'create')
        ->where('module', 'Manajemen User')
        ->where('auditable_id', $user->id)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->new_values)->not->toHaveKey('password')
        ->and($log->new_values)->not->toHaveKey('remember_token')
        ->and($log->new_values)->not->toHaveKey('fcm_token')
        ->and($log->new_values)->toHaveKey('username');

    // Periksa juga JSON mentah di database, bukan hanya hasil cast model.
    $mentah = (string) DB::table('audit_logs')->where('id', $log->id)->value('new_values');

    expect($mentah)->not->toContain('password')
        ->and($mentah)->not->toContain('token-fcm-yang-tidak-boleh-tersimpan')
        ->and($mentah)->not->toContain('remember-yang-tidak-boleh-tersimpan');
});

test('perubahan user tidak membocorkan password di nilai lama maupun baru', function () {
    $admin = userKlinik('Admin');
    $this->actingAs($admin);

    $target = userKlinik('Kasir');

    $target->update([
        'password' => 'PasswordBaru456!',
        'username' => 'username_baru_sekali',
    ]);

    $log = AuditLog::where('action', 'update')
        ->where('module', 'Manajemen User')
        ->where('auditable_id', $target->id)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull();

    $old = $log->old_values ?? [];
    $new = $log->new_values ?? [];

    expect($old)->not->toHaveKey('password')
        ->and($new)->not->toHaveKey('password')
        ->and($new)->toHaveKey('username');

    $mentah = (string) DB::table('audit_logs')->where('id', $log->id)->value('new_values');
    expect($mentah)->not->toContain('password');
});

test('log login gagal tidak menyimpan kredensial sama sekali', function () {
    $user = userKlinik('Admin');

    $this->post('/login', [
        'login' => $user->email,
        'password' => 'PasswordSalahYangTidakBolehTersimpan!',
    ]);

    $this->assertGuest();

    $log = AuditLog::where('action', 'login_failed')->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->old_values)->toBeNull()
        ->and($log->new_values)->toBeNull();

    $mentah = (string) DB::table('audit_logs')->where('id', $log->id)->value('new_values');
    expect($mentah)->not->toContain('PasswordSalahYangTidakBolehTersimpan');
});

test('perubahan pengaturan password tidak dicatat isinya', function () {
    $admin = userKlinik('Admin');

    $this->actingAs($admin)->put(route('settings.password.update'), [
        'current_password' => 'Password123!',
        'password' => 'PasswordBaru789!',
        'password_confirmation' => 'PasswordBaru789!',
    ]);

    $log = AuditLog::where('action', 'update')
        ->where('module', 'Pengaturan Sistem')
        ->where('user_id', $admin->id)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->old_values)->toBeNull()
        ->and($log->new_values)->toBeNull();

    $mentah = (string) DB::table('audit_logs')->where('id', $log->id)->value('new_values');
    expect($mentah)->not->toContain('PasswordBaru789');
});
