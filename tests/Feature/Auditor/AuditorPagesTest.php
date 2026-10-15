<?php

use App\Models\AuditLog;
use App\Models\EMR;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Poli;
use Illuminate\Support\Str;

/**
 * Memastikan halaman auditor benar-benar menampilkan data yang diminta:
 * perbandingan nilai lama vs baru, filter server-side, dan riwayat per entitas.
 */

test('halaman detail menampilkan perbandingan nilai lama dan baru', function () {
    $auditor = userKlinik('Auditor');

    // Buat log update nyata lewat observer (sebagai admin).
    $this->actingAs(userKlinik('Admin'));
    $pasien = pasienKlinik(userKlinik('Pasien'), ['nama_pasien' => 'Nama Awal Uji']);
    $pasien->update(['nama_pasien' => 'Nama Akhir Uji', 'alamat' => 'Jalan Baru Uji']);

    $log = AuditLog::where('action', 'update')->where('module', 'Pasien')->latest('id')->first();

    expect($log)->not->toBeNull();

    $this->actingAs($auditor)
        ->get(route('auditor.logs.show', $log->id))
        ->assertOk()
        ->assertSee('nama_pasien')
        ->assertSee('Nama Awal Uji')
        ->assertSee('Nama Akhir Uji')
        ->assertSee('alamat')
        ->assertSee('Jalan Baru Uji');
});

test('tabel audit log memakai pagination dan filter di sisi server', function () {
    $auditor = userKlinik('Auditor');

    $this->actingAs(userKlinik('Admin'));
    pasienKlinik(userKlinik('Pasien'), ['nama_pasien' => 'Pasien Filter Uji']);

    // Filter modul + action + kata kunci.
    $response = $this->actingAs($auditor)->getJson(route('auditor.logs.data', [
        'module' => 'Pasien',
        'action' => 'create',
        'keyword' => 'Pasien Filter Uji',
        'length' => 10,
        'start' => 0,
        'draw' => 1,
    ]))->assertOk();

    $rows = $response->json('data');

    expect($rows)->not->toBeEmpty();

    foreach ($rows as $row) {
        expect($row['module'])->toBe('Pasien')
            ->and($row['user_role'])->toBe('Admin')
            ->and($row['created_at'])->not->toBeEmpty();
    }

    // Kata kunci yang tidak mungkin cocok harus menghasilkan 0 baris.
    $kosong = $this->actingAs($auditor)->getJson(route('auditor.logs.data', [
        'keyword' => 'zzz-tidak-mungkin-ada-zzz',
    ]))->assertOk();

    expect($kosong->json('recordsFiltered'))->toBe(0);
});

test('filter rentang tanggal dan role membatasi hasil', function () {
    $auditor = userKlinik('Auditor');

    $this->actingAs(userKlinik('Admin'));
    pasienKlinik(userKlinik('Pasien'), ['nama_pasien' => 'Pasien Rentang Uji']);

    // Rentang tanggal di masa depan → tidak ada aktivitas.
    $depan = $this->actingAs($auditor)->getJson(route('auditor.logs.data', [
        'from' => now()->addYears(5)->toDateString(),
        'to' => now()->addYears(5)->addDay()->toDateString(),
    ]))->assertOk();

    expect($depan->json('recordsFiltered'))->toBe(0);

    // Rentang hari ini → ada aktivitas.
    $hariIni = $this->actingAs($auditor)->getJson(route('auditor.logs.data', [
        'from' => now()->toDateString(),
        'to' => now()->toDateString(),
        'role' => 'Admin',
    ]))->assertOk();

    expect($hariIni->json('recordsFiltered'))->toBeGreaterThan(0);

    foreach ($hariIni->json('data') as $row) {
        expect($row['user_role'])->toBe('Admin');
    }
});

test('halaman riwayat per entitas menampilkan perubahan dan siapa saja yang mengakses', function () {
    $auditor = userKlinik('Auditor');
    $admin = userKlinik('Admin');

    $this->actingAs($admin);
    $pasien = pasienKlinik(userKlinik('Pasien'), ['nama_pasien' => 'Pasien Riwayat Uji']);
    $pasien->update(['nama_pasien' => 'Pasien Riwayat Uji Diubah']);

    $poli = Poli::create(['nama_poli' => 'Poli Riwayat '.Str::random(4)]);
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

    // Admin membaca data pasien dan rekam medisnya.
    $this->actingAs($admin)->get(route('manajemen_pengguna.get_pasien_by_id', $pasien->id))->assertOk();
    $this->actingAs($admin)->get(route('data_medis_pasien.get.data.emr.by.id', $emr->id))->assertOk();

    $response = $this->actingAs($auditor)
        ->get(route('auditor.logs.entity', ['no_emr' => $pasien->no_emr]))
        ->assertOk();

    // Perubahan data pasien muncul di timeline.
    $response->assertSee('Pasien Riwayat Uji Diubah')
        ->assertSee('Pasien Riwayat Uji');

    // Nama pengakses muncul di daftar "siapa saja yang mengakses".
    $response->assertSee($admin->username);
});

test('export audit log mengikuti filter yang aktif', function () {
    $auditor = userKlinik('Auditor');

    $response = $this->actingAs($auditor)
        ->get(route('auditor.logs.export', ['format' => 'csv', 'module' => 'Audit Log']))
        ->assertOk();

    expect($response->headers->get('content-disposition'))->toContain('audit_log_');

    // Aksi export oleh auditor ikut tercatat.
    expect(
        AuditLog::where('action', 'export')
            ->where('module', 'Audit Log')
            ->where('user_id', $auditor->id)
            ->exists()
    )->toBeTrue();
});

test('pasien yang tidak ada tetap menampilkan pesan informatif', function () {
    $auditor = userKlinik('Auditor');

    $this->actingAs($auditor)
        ->get(route('auditor.logs.entity', ['no_emr' => 'RM-TIDAK-ADA-999']))
        ->assertOk()
        ->assertSee('tidak ditemukan');
});
