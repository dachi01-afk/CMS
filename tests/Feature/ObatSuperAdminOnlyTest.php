<?php

namespace Tests\Feature;

use App\Models\JenisSpesialis;
use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Perubahan langsung DATA OBAT tetap hanya untuk Super Admin.
 *
 * Farmasi boleh menekan Edit/Hapus, tetapi aksinya menjadi pengajuan yang
 * harus disetujui Verifikator (lihat PengajuanPerubahanDataTest).
 *
 * Di luar data obat, tombol Edit/Hapus kembali normal:
 *  - Admin tetap bisa mengedit/menghapus jenis spesialis, dsb.
 *  - Farmasi tetap bisa mengedit/menghapus kategori obat, dsb.
 */
class ObatSuperAdminOnlyTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        return User::create([
            'username' => 'tmp_'.str_replace(' ', '_', strtolower($role)).'_'.uniqid(),
            'email' => uniqid().'@example.test',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function obat(): Obat
    {
        return Obat::create([
            'kode_obat' => 'TST-'.uniqid(),
            'nama_obat' => 'Obat Uji',
            'jumlah' => 10,
            'dosis' => 1,
            'total_harga' => 1000,
            'harga_jual_obat' => 1500,
        ]);
    }

    private function ubahObat(Obat $obat)
    {
        return $this->postJson('/farmasi/obat/update-data-obat/'.$obat->id, [
            'nama_obat' => 'Obat Uji Diubah',
            'harga_jual_obat' => 2500,
        ]);
    }

    public function test_super_admin_bisa_edit_dan_hapus_obat(): void
    {
        $superAdmin = $this->user('Super Admin');

        $obat = $this->obat();
        $this->actingAs($superAdmin)->postJson('/farmasi/obat/update-data-obat/'.$obat->id, [
            'nama_obat' => 'Obat Uji Diubah',
            'harga_jual_obat' => 2500,
        ])->assertOk();

        $this->assertSame('Obat Uji Diubah', $obat->fresh()->nama_obat);

        $obatHapus = $this->obat();
        $this->actingAs($superAdmin)
            ->deleteJson('/farmasi/obat/delete-data-obat/'.$obatHapus->id)
            ->assertOk();

        $this->assertNull(Obat::find($obatHapus->id));
    }

    public function test_farmasi_bisa_mengajukan_edit_dan_hapus_obat(): void
    {
        $farmasi = $this->user('Farmasi');

        $obat = $this->obat();
        $this->actingAs($farmasi)->postJson('/farmasi/obat/update-data-obat/'.$obat->id, [
            'nama_obat' => 'Obat Uji Diubah',
            'harga_jual_obat' => 2500,
        ])->assertOk();

        // Data obat belum berubah sebelum disetujui Verifikator.
        $this->assertSame('Obat Uji', $obat->fresh()->nama_obat);
        $this->assertDatabaseHas('pengajuan_perubahan_data', [
            'jenis_data' => 'obat',
            'aksi' => 'edit',
            'target_id' => $obat->id,
            'status' => 'menunggu',
            'diajukan_oleh' => $farmasi->id,
        ]);

        $this->actingAs($farmasi)
            ->deleteJson('/farmasi/obat/delete-data-obat/'.$obat->id)
            ->assertOk();

        $this->assertNotNull(Obat::find($obat->id));
        $this->assertDatabaseHas('pengajuan_perubahan_data', [
            'jenis_data' => 'obat',
            'aksi' => 'hapus',
            'target_id' => $obat->id,
            'status' => 'menunggu',
            'diajukan_oleh' => $farmasi->id,
        ]);
    }

    public function test_admin_tidak_bisa_edit_dan_hapus_obat(): void
    {
        $admin = $this->user('Admin');

        $obat = $this->obat();
        $this->actingAs($admin)->postJson('/farmasi/obat/update-data-obat/'.$obat->id, [
            'nama_obat' => 'Obat Uji Diubah',
            'harga_jual_obat' => 2500,
        ])->assertForbidden();

        $this->actingAs($admin)
            ->deleteJson('/farmasi/obat/delete-data-obat/'.$obat->id)
            ->assertForbidden();

        $this->assertSame('Obat Uji', $obat->fresh()->nama_obat);
    }

    public function test_tombol_edit_hapus_obat_aktif_untuk_farmasi_sebagai_pengajuan(): void
    {
        $obat = $this->obat();

        $rows = $this->actingAs($this->user('Farmasi'))
            ->getJson('/farmasi/obat/get-data-obat')
            ->assertOk()
            ->json('data');

        $html = collect($rows)->firstWhere('kode', $obat->kode_obat)['action'] ?? '';

        $this->assertStringContainsString('btn-update-obat', $html);
        $this->assertStringContainsString('btn-delete-obat', $html);
        $this->assertStringNotContainsString('aria-disabled', $html);
        $this->assertStringNotContainsString('disabled', $html);
        $this->assertSame(3, substr_count($html, 'data-id="'.$obat->id.'"'));
        $this->assertStringContainsString('Ajukan Edit Obat', $html);
        $this->assertStringContainsString('Ajukan Hapus Obat', $html);

        // Tombol Detail tetap aktif.
        $this->assertStringContainsString('btn-detail-obat', $html);
    }

    public function test_tombol_edit_hapus_obat_aktif_untuk_super_admin(): void
    {
        $obat = $this->obat();

        $rows = $this->actingAs($this->user('Super Admin'))
            ->getJson('/farmasi/obat/get-data-obat')
            ->assertOk()
            ->json('data');

        $html = collect($rows)->firstWhere('kode', $obat->kode_obat)['action'] ?? '';

        $this->assertStringContainsString('btn-update-obat', $html);
        $this->assertStringContainsString('btn-delete-obat', $html);
        $this->assertStringNotContainsString('disabled', $html);
        $this->assertSame(3, substr_count($html, 'data-id="'.$obat->id.'"'));
    }

    public function test_admin_kembali_bisa_edit_dan_hapus_jenis_spesialis(): void
    {
        $admin = $this->user('Admin');
        $jenis = JenisSpesialis::create(['nama_spesialis' => 'Spesialis Uji '.uniqid()]);

        $this->actingAs($admin)->postJson('/jenis-spesialis/update-data-jenis-spesialis', [
            'id' => $jenis->id,
            'nama_spesialis' => 'Diubah Admin',
        ])->assertOk();

        $this->assertSame('Diubah Admin', $jenis->fresh()->nama_spesialis);

        $this->actingAs($admin)
            ->postJson('/jenis-spesialis/delete-data-jenis-spesialis/'.$jenis->id)
            ->assertOk();

        $this->assertNull(JenisSpesialis::find($jenis->id));
    }

    public function test_admin_melihat_tombol_edit_hapus_aktif_di_tabel(): void
    {
        JenisSpesialis::create(['nama_spesialis' => 'Spesialis Uji '.uniqid()]);

        $rows = $this->actingAs($this->user('Admin'))
            ->getJson('/jenis-spesialis/data-jenis-spesialis')
            ->assertOk()
            ->json('data');

        $action = $rows[0]['action'];

        $this->assertStringNotContainsString('aria-disabled', $action);
        $this->assertStringNotContainsString('disabled', $action);
        $this->assertStringContainsString('data-id=', $action);
    }

    public function test_farmasi_kembali_bisa_edit_dan_hapus_kategori_obat(): void
    {
        $farmasi = $this->user('Farmasi');
        $kategori = KategoriObat::create(['nama_kategori_obat' => 'Kategori Uji '.uniqid()]);

        $this->actingAs($farmasi)->postJson('/farmasi/kategori-obat/update-data-kategori-obat', [
            'id' => $kategori->id,
            'nama_kategori_obat' => 'Kategori Diubah',
        ])->assertOk();

        $this->assertSame('Kategori Diubah', $kategori->fresh()->nama_kategori_obat);

        $this->actingAs($farmasi)->postJson('/farmasi/kategori-obat/delete-data-kategori-obat', [
            'id' => $kategori->id,
        ])->assertOk();

        $this->assertNull(KategoriObat::find($kategori->id));
    }

    /** Ambil potongan tag tombol Import dari HTML halaman obat. */
    private function importButton(string $html): string
    {
        preg_match('/<button[^>]*id="btn-import"[^>]*>/s', $html, $match);

        return $match[0] ?? '';
    }

    public function test_farmasi_tidak_bisa_import_excel_obat(): void
    {
        $this->actingAs($this->user('Farmasi'))
            ->post('/farmasi/obat/import-data-obat', [])
            ->assertForbidden();

        // Super Admin tetap bisa masuk ke proses import (302 = validasi file).
        $this->actingAs($this->user('Super Admin'))
            ->post('/farmasi/obat/import-data-obat', [])
            ->assertStatus(302);
    }

    public function test_farmasi_tidak_bisa_mengubah_stok_obat_lewat_api(): void
    {
        $obat = $this->obat();

        Sanctum::actingAs($this->user('Farmasi'));
        $this->putJson('/api/penjualan-obat/update-stok/'.$obat->id, [
            'jumlah' => 99,
            'action' => 'set',
        ])->assertForbidden();

        $this->assertSame(10, (int) $obat->fresh()->jumlah);

        Sanctum::actingAs($this->user('Super Admin'));
        $this->putJson('/api/penjualan-obat/update-stok/'.$obat->id, [
            'jumlah' => 99,
            'action' => 'set',
        ])->assertOk();

        $this->assertSame(99, (int) $obat->fresh()->jumlah);
    }

    public function test_farmasi_tidak_bisa_memperbaiki_stok_obat(): void
    {
        $this->actingAs($this->user('Farmasi'))
            ->postJson('/farmasi/depot/repair-stok-obat', [])
            ->assertForbidden();

        // Super Admin lolos penjagaan (bukan 403).
        $superAdmin = $this->actingAs($this->user('Super Admin'))
            ->postJson('/farmasi/depot/repair-stok-obat', []);

        $this->assertNotSame(403, $superAdmin->getStatusCode());
    }

    public function test_tombol_simpan_perbaikan_stok_nonaktif_untuk_farmasi(): void
    {
        $page = $this->actingAs($this->user('Farmasi'))->get('/farmasi/depot');
        $page->assertOk();

        preg_match('/<button[^>]*id="btn-save-repair-obat"[^>]*>/s', $page->getContent(), $match);

        $this->assertArrayHasKey(0, $match, 'Tombol simpan perbaikan stok tidak ditemukan.');
        $this->assertMatchesRegularExpression('/\sdisabled[\s>]/', $match[0]);
    }

    public function test_farmasi_tetap_bisa_menambah_obat_baru(): void
    {
        // 422 = lolos penjagaan role & middleware, hanya payload uji tidak lengkap.
        $this->actingAs($this->user('Farmasi'))
            ->postJson('/farmasi/obat/create-data-obat', ['nama_obat' => 'Obat Baru Uji'])
            ->assertStatus(422);
    }

    public function test_tombol_import_nonaktif_untuk_farmasi(): void
    {
        $farmasiPage = $this->actingAs($this->user('Farmasi'))->get('/farmasi/obat');
        $farmasiPage->assertOk();

        $this->assertMatchesRegularExpression('/\sdisabled[\s>]/', $this->importButton($farmasiPage->getContent()));

        $superAdminPage = $this->actingAs($this->user('Super Admin'))->get('/farmasi/obat');
        $superAdminPage->assertOk();

        $this->assertDoesNotMatchRegularExpression('/\sdisabled[\s>]/', $this->importButton($superAdminPage->getContent()));
    }

    public function test_admin_melihat_tombol_edit_kunjungan_aktif(): void
    {
        $page = $this->actingAs($this->user('Admin'))->get('/jadwal_kunjungan');

        $page->assertOk();
        $this->assertStringNotContainsString('data-can-manage', $page->getContent());
    }
}
