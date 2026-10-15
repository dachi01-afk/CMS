<?php

use App\Models\AuditLog;
use App\Models\HasilLab;
use App\Models\Pasien;
use App\Support\Audit\AuditContext;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

/**
 * Memastikan SEMUA aktivitas masuk ke audit_logs, termasuk jalur yang
 * sebelumnya lolos: raw query (DB::table), update/delete massal, aplikasi
 * mobile (API), dan akses halaman.
 */

/** Membuat rantai pasien → order lab → detail → hasil lab dengan raw query. */
function rantaiLab(Pasien $pasien): array
{
    $now = now()->toDateTimeString();

    $satuanId = DB::table('satuan_lab')->insertGetId(['nama_satuan' => 'mg/dL', 'created_at' => $now]);
    $jenisId = DB::table('jenis_pemeriksaan_lab')->insertGetId([
        'kode_pemeriksaan' => 'GLU', 'nama_pemeriksaan' => 'Gula Darah', 'nilai_normal' => 100,
        'harga_pemeriksaan_lab' => 50000, 'status' => 'Active', 'satuan_lab_id' => $satuanId,
    ]);
    $orderId = DB::table('order_lab')->insertGetId([
        'pasien_id' => $pasien->id, 'no_order_lab' => 'LAB-'.uniqid(), 'tanggal_order' => today()->toDateString(),
        'tanggal_pemeriksaan' => today()->toDateString(), 'jam_pemeriksaan' => '08:00:00', 'status' => 'Pending',
    ]);
    $detailId = DB::table('order_lab_detail')->insertGetId([
        'order_lab_id' => $orderId, 'jenis_pemeriksaan_lab_id' => $jenisId, 'status_pemeriksaan' => 'Pending',
    ]);
    $hasilId = DB::table('hasil_lab')->insertGetId([
        'order_lab_detail_id' => $detailId, 'nilai_hasil' => 120, 'nilai_rujukan' => '70-100',
        'tanggal_pemeriksaan' => today()->toDateString(), 'jam_pemeriksaan' => '09:00:00',
    ]);

    return compact('orderId', 'detailId', 'hasilId');
}

test('raw query DB::table tercatat lengkap dengan pelaku dan pasien terkait', function () {
    $perawat = userKlinik('Perawat');
    $this->actingAs($perawat);

    $pasien = pasienKlinik(userKlinik('Pasien'), ['nama_pasien' => 'Siti Aminah']);
    $ids = rantaiLab($pasien);

    $create = AuditLog::where('action', 'create')->where('auditable_type', HasilLab::class)
        ->where('auditable_id', $ids['hasilId'])->first();

    expect($create)->not->toBeNull()
        ->and($create->user_id)->toBe($perawat->id)
        ->and($create->user_role)->toBe('Perawat')
        ->and($create->module)->toBe('Hasil Lab')
        // hasil_lab → order_lab_detail → order_lab → pasien
        ->and($create->pasien_id)->toBe($pasien->id)
        ->and($create->new_values['nilai_hasil'])->toEqual(120);

    DB::table('hasil_lab')->where('id', $ids['hasilId'])->update(['nilai_hasil' => 95]);

    $update = AuditLog::where('action', 'update')->where('auditable_type', HasilLab::class)->latest('id')->first();

    expect($update)->not->toBeNull()
        ->and($update->old_values['nilai_hasil'])->toEqual(120)
        ->and($update->new_values['nilai_hasil'])->toEqual(95)
        ->and($update->description)->toContain('Field berubah: nilai_hasil')
        ->and($update->pasien_id)->toBe($pasien->id);
});

test('hapus massal Model::where()->delete() tercatat per baris', function () {
    $this->actingAs(userKlinik('Kasir'));

    $pasien = pasienKlinik(userKlinik('Pasien'));
    $ids = rantaiLab($pasien);

    HasilLab::where('order_lab_detail_id', $ids['detailId'])->delete();

    $delete = AuditLog::where('action', 'delete')->where('auditable_type', HasilLab::class)
        ->where('auditable_id', $ids['hasilId'])->first();

    expect($delete)->not->toBeNull()
        ->and($delete->user_role)->toBe('Kasir')
        ->and($delete->old_values)->toHaveKey('nilai_hasil')
        ->and($delete->pasien_id)->toBe($pasien->id);
});

test('perubahan password lewat raw query terlihat tanpa menyimpan isinya', function () {
    $admin = userKlinik('Admin');
    $this->actingAs($admin);
    $target = userKlinik('Kasir');

    DB::table('user')->where('id', $target->id)->update(['password' => bcrypt('Rahasia123!')]);

    $log = AuditLog::where('action', 'update')->where('auditable_id', $target->id)
        ->where('auditable_type', App\Models\User::class)->latest('id')->first();

    $raw = DB::table('audit_logs')->where('id', $log->id)->first();

    expect($log->description)->toContain('password')
        ->and((string) $raw->old_values.(string) $raw->new_values)->not->toContain('$2y$');
});

test('audit_logs tidak bisa diubah atau dihapus bahkan lewat query langsung', function () {
    $this->actingAs(userKlinik('Super Admin'));
    pasienKlinik(userKlinik('Pasien'));

    $id = AuditLog::max('id');

    expect(fn () => DB::table('audit_logs')->where('id', $id)->update(['description' => 'diubah']))
        ->toThrow(QueryException::class);

    expect(fn () => DB::table('audit_logs')->where('id', $id)->delete())
        ->toThrow(QueryException::class);
});

test('aktivitas tanpa user login tercatat atas nama Sistem dengan waktu lokal', function () {
    AuditContext::startSystem();

    $obatId = DB::table('obat')->insertGetId([
        'nama_obat' => 'Obat Uji Sistem', 'jumlah' => 5, 'dosis' => 1, 'total_harga' => 1000,
    ]);

    $log = AuditLog::where('auditable_type', App\Models\Obat::class)->where('auditable_id', $obatId)->first();

    expect($log->user_name)->toBe('Sistem')
        ->and($log->source)->toBe('sistem')
        ->and(abs($log->created_at->diffInMinutes(now())))->toBeLessThan(2);
});

test('membuka halaman web tercatat, AJAX pengisi tabel tidak, AJAX detail tercatat', function () {
    $admin = userKlinik('Admin');

    $this->actingAs($admin)->get('/settings')->assertOk();

    expect(AuditLog::where('action', 'view')->where('user_id', $admin->id)
        ->where('description', 'like', 'Membuka halaman Settings%')->where('source', 'web')->exists())->toBeTrue();

    $before = AuditLog::where('user_id', $admin->id)->count();
    $this->actingAs($admin)->getJson('/manajemen_pengguna/data_admin');
    expect(AuditLog::where('user_id', $admin->id)->count())->toBe($before);

    $adminId = DB::table('admin')->insertGetId(['user_id' => $admin->id, 'nama_admin' => 'Admin Uji', 'no_hp' => '0812']);
    $this->actingAs($admin)->getJson('/manajemen_pengguna/get_admin_by_id/'.$adminId)->assertOk();

    expect(AuditLog::where('action', 'view')->where('user_id', $admin->id)
        ->where('description', 'like', '%id: '.$adminId.'%')->exists())->toBeTrue();
});

test('akses yang ditolak (403) tercatat', function () {
    $kasir = userKlinik('Kasir');

    $this->actingAs($kasir)->getJson('/manajemen_pengguna/data_admin')->assertForbidden();

    expect(AuditLog::where('action', 'access_denied')->where('user_id', $kasir->id)->exists())->toBeTrue();
});

test('login, login gagal, dan logout aplikasi mobile tercatat', function () {
    $user = userKlinik('Pasien');

    $this->postJson('/api/login', ['username' => $user->username, 'password' => 'salah-sekali'])->assertStatus(401);

    expect(AuditLog::where('action', 'login_failed')->where('user_id', $user->id)
        ->where('source', 'mobile')->exists())->toBeTrue();

    $token = $this->postJson('/api/login', ['username' => $user->username, 'password' => 'Password123!'])
        ->assertOk()->json('data.token') ?? $user->tokens()->latest('id')->first()?->id;

    expect(AuditLog::where('action', 'login')->where('user_id', $user->id)->where('source', 'mobile')->exists())->toBeTrue();

    Sanctum::actingAs($user);
    $this->postJson('/api/logout')->assertOk();

    expect(AuditLog::where('action', 'logout')->where('user_id', $user->id)->where('source', 'mobile')->exists())->toBeTrue();

    $raw = DB::table('audit_logs')->where('user_id', $user->id)->get()->toJson();
    expect($raw)->not->toContain('Password123!')->not->toContain('salah-sekali');
});

test('membaca data dari aplikasi mobile tercatat dengan sumber mobile', function () {
    $user = userKlinik('Pasien');
    Sanctum::actingAs($user);

    $this->getJson('/api/penjualan-obat/obat/list')->assertOk();

    $log = AuditLog::where('action', 'view')->where('user_id', $user->id)->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->source)->toBe('mobile')
        ->and($log->description)->toContain('aplikasi mobile');
});

test('perubahan data lewat API mobile tercatat atas nama user token', function () {
    $user = userKlinik('Farmasi');
    Sanctum::actingAs($user);

    $obatId = DB::table('obat')->insertGetId([
        'nama_obat' => 'Paracetamol Uji', 'jumlah' => 10, 'dosis' => 500, 'total_harga' => 5000,
    ]);

    $this->putJson('/api/penjualan-obat/update-stok/'.$obatId, ['jumlah' => 3, 'action' => 'subtract'])->assertOk();

    $log = AuditLog::where('action', 'update')->where('auditable_id', $obatId)
        ->where('auditable_type', App\Models\Obat::class)->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($user->id)
        ->and($log->source)->toBe('mobile')
        ->and($log->old_values['jumlah'])->toEqual(10)
        ->and($log->new_values['jumlah'])->toEqual(7);
});

test('auditor tidak bisa mengubah data lewat API maupun login di aplikasi mobile', function () {
    $auditor = userKlinik('Auditor');

    $obatId = DB::table('obat')->insertGetId([
        'nama_obat' => 'Obat Terlindungi', 'jumlah' => 10, 'dosis' => 1, 'total_harga' => 1000,
    ]);

    Sanctum::actingAs($auditor);
    $this->putJson('/api/penjualan-obat/update-stok/'.$obatId, ['jumlah' => 99, 'action' => 'set'])->assertForbidden();

    expect(DB::table('obat')->where('id', $obatId)->value('jumlah'))->toBe(10);

    app('auth')->forgetGuards();

    $this->postJson('/api/login', ['username' => $auditor->username, 'password' => 'Password123!'])->assertForbidden();

    expect($auditor->tokens()->count())->toBe(0)
        ->and(AuditLog::where('action', 'login_failed')->where('user_id', $auditor->id)->exists())->toBeTrue();
});

test('riwayat per entitas menampilkan semua aktivitas pasien termasuk hasil lab', function () {
    $this->actingAs(userKlinik('Perawat'));
    $pasien = pasienKlinik(userKlinik('Pasien'), ['nama_pasien' => 'Pasien Riwayat Lengkap']);
    rantaiLab($pasien);

    $auditor = userKlinik('Auditor');

    $this->actingAs($auditor)
        ->get(route('auditor.logs.entity', ['no_emr' => $pasien->no_emr]))
        ->assertOk()
        ->assertSee('Pasien Riwayat Lengkap')
        ->assertSee('Menambah data Hasil Lab')
        ->assertSee('Menambah data Order Lab');

    $this->actingAs($auditor)
        ->get(route('auditor.logs.entity', ['no_emr' => $pasien->no_emr, 'module' => 'Hasil Lab']))
        ->assertOk()
        ->assertSee('Menambah data Hasil Lab')
        ->assertDontSee('Menambah data Order Lab Detail');
});

test('hanya role Farmasi yang bisa mengubah stok obat lewat API mobile', function () {
    $obatId = DB::table('obat')->insertGetId([
        'nama_obat' => 'Amoxicillin Uji Role', 'jumlah' => 50, 'dosis' => 500, 'total_harga' => 5000,
    ]);

    foreach (['Pasien', 'Dokter', 'Perawat', 'Kasir', 'Admin'] as $role) {
        $user = userKlinik($role);
        Sanctum::actingAs($user);

        $this->putJson('/api/penjualan-obat/update-stok/'.$obatId, ['jumlah' => 0, 'action' => 'set'])
            ->assertForbidden();

        expect(DB::table('obat')->where('id', $obatId)->value('jumlah'))->toBe(50)
            ->and(AuditLog::where('action', 'access_denied')->where('user_id', $user->id)->exists())->toBeTrue();

        app('auth')->forgetGuards();
    }

    Sanctum::actingAs(userKlinik('Farmasi'));

    $this->putJson('/api/penjualan-obat/update-stok/'.$obatId, ['jumlah' => 5, 'action' => 'add'])->assertOk();

    expect(DB::table('obat')->where('id', $obatId)->value('jumlah'))->toBe(55);
});
