<?php

use App\Models\User;

/**
 * Memastikan pembatasan read-only auditor ditegakkan di backend,
 * bukan hanya dengan menyembunyikan menu.
 */

test('auditor bisa membuka halaman audit log dan dashboard aktivitas', function () {
    $auditor = userKlinik('Auditor');

    $this->actingAs($auditor)->get(route('auditor.logs.index'))->assertOk();
    $this->actingAs($auditor)->get(route('auditor.logs.entity'))->assertOk();
    $this->actingAs($auditor)->get(route('auditor.dashboard'))->assertOk();
});

test('auditor bisa membaca endpoint data log (server-side pagination)', function () {
    $auditor = userKlinik('Auditor');

    $this->actingAs($auditor)
        ->getJson(route('auditor.logs.data'))
        ->assertOk()
        ->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
});

test('auditor ditolak saat mencoba mengubah data lewat endpoint yang hanya butuh login', function () {
    $auditor = userKlinik('Auditor');

    // PUT /settings/profile hanya dilindungi middleware 'auth',
    // jadi tanpa penjaga auditor request ini akan sampai ke controller.
    $this->actingAs($auditor)
        ->put(route('settings.profile.update'), ['username' => 'hacker'])
        ->assertForbidden();

    // POST /heartbeat juga hanya dilindungi 'auth'.
    $this->actingAs($auditor)->post('/heartbeat')->assertForbidden();

    // Ganti password sendiri pun ditolak: auditor tidak boleh mengubah data apa pun.
    $this->actingAs($auditor)->put('/password', [
        'current_password' => 'Password123!',
        'password' => 'Password456!',
        'password_confirmation' => 'Password456!',
    ])->assertForbidden();
});

test('auditor ditolak pada endpoint milik role lain', function () {
    $auditor = userKlinik('Auditor');

    $this->actingAs($auditor)->post(route('manajemen_pengguna.add_user'), [
        'username' => 'penyusup',
        'email' => 'penyusup@example.test',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'Admin',
    ])->assertForbidden();

    $this->assertDatabaseMissing('user', ['email' => 'penyusup@example.test']);
});

test('percobaan tulis auditor tidak mengubah data apa pun', function () {
    $auditor = userKlinik('Auditor');
    $korban = userKlinik('Kasir');

    $usernameAwal = $korban->username;
    $jumlahUser = User::count();

    $this->actingAs($auditor)->put(route('settings.profile.update'), ['username' => 'diubah-paksa'])->assertForbidden();
    $this->actingAs($auditor)->delete(route('manajemen_pengguna.delete_user', $korban->id))->assertForbidden();
    $this->actingAs($auditor)->post(route('manajemen_pengguna.add_user'), [])->assertForbidden();

    expect($korban->fresh()->username)->toBe($usernameAwal);
    expect(User::count())->toBe($jumlahUser);
});

test('auditor tetap bisa logout', function () {
    $auditor = userKlinik('Auditor');

    $this->actingAs($auditor)->post(route('logout'))->assertRedirect('/');

    $this->assertGuest();
});

test('role lain tidak terpengaruh penjaga read-only auditor', function () {
    $admin = userKlinik('Admin');

    // Admin lolos penjaga auditor (responsnya validasi, bukan 403).
    $response = $this->actingAs($admin)->put(route('settings.profile.update'), []);

    expect($response->status())->not->toBe(403);
});

test('halaman settings mengarahkan auditor ke dashboard aktivitas', function () {
    $auditor = userKlinik('Auditor');

    $this->actingAs($auditor)
        ->get(route('settings.index'))
        ->assertRedirect(route('auditor.dashboard'));
});
