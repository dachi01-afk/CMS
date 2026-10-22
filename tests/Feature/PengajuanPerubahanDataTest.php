<?php

namespace Tests\Feature;

use App\Models\KategoriLayanan;
use App\Models\Layanan;
use App\Models\Obat;
use App\Models\PengajuanPerubahanData;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Alur edit/hapus tidak lagi langsung mengubah data untuk Admin (layanan)
 * dan Farmasi (obat). Perubahan harus disetujui role Verifikator.
 */
class PengajuanPerubahanDataTest extends TestCase
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

    private function layanan(): Layanan
    {
        $kategori = KategoriLayanan::create([
            'nama_kategori' => 'Kategori Uji '.uniqid(),
            'deskripsi_kategori' => 'Kategori untuk pengujian pengajuan.',
            'status_kategori' => 'Aktif',
        ]);

        return Layanan::create([
            'kategori_layanan_id' => $kategori->id,
            'nama_layanan' => 'Layanan Uji',
            'harga_sebelum_diskon' => 10000,
            'is_global' => 1,
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

    private function pengajuan(string $jenis, string $aksi, int $targetId): PengajuanPerubahanData
    {
        return PengajuanPerubahanData::query()
            ->where('jenis_data', $jenis)
            ->where('aksi', $aksi)
            ->where('target_id', $targetId)
            ->latest()
            ->firstOrFail();
    }

    public function test_edit_layanan_oleh_admin_menunggu_persetujuan_verifikator(): void
    {
        $admin = $this->user('Admin');
        $verifikator = $this->user('Verifikator');
        $layanan = $this->layanan();

        $this->actingAs($admin)->postJson('/layanan/update-data-layanan', [
            'id' => $layanan->id,
            'kategori_layanan_id' => $layanan->kategori_layanan_id,
            'nama_layanan' => 'Layanan Setelah Pengajuan',
            'harga_sebelum_diskon' => 20000,
            'is_global' => 1,
        ])->assertOk();

        // Data asli belum berubah.
        $this->assertSame('Layanan Uji', $layanan->fresh()->nama_layanan);

        $pengajuan = $this->pengajuan('layanan', 'edit', $layanan->id);
        $this->assertSame('menunggu', $pengajuan->status);
        $this->assertSame($admin->id, $pengajuan->diajukan_oleh);

        $this->actingAs($verifikator)
            ->postJson("/verifikator/pengajuan/{$pengajuan->id}/setujui", [])
            ->assertOk();

        $layanan->refresh();
        $this->assertSame('Layanan Setelah Pengajuan', $layanan->nama_layanan);
        $this->assertSame(20000.0, (float) $layanan->harga_sebelum_diskon);
        $this->assertSame('disetujui', $pengajuan->fresh()->status);
    }

    public function test_hapus_layanan_oleh_admin_baru_diterapkan_setelah_disetujui(): void
    {
        $admin = $this->user('Admin');
        $verifikator = $this->user('Verifikator');
        $layanan = $this->layanan();

        $this->actingAs($admin)->postJson('/layanan/delete-data-layanan', [
            'id' => $layanan->id,
        ])->assertOk();

        $this->assertNotNull(Layanan::find($layanan->id));

        $pengajuan = $this->pengajuan('layanan', 'hapus', $layanan->id);
        $this->assertSame('menunggu', $pengajuan->status);

        $this->actingAs($verifikator)
            ->postJson("/verifikator/pengajuan/{$pengajuan->id}/setujui", [])
            ->assertOk();

        $this->assertNull(Layanan::find($layanan->id));
        $this->assertSame('disetujui', $pengajuan->fresh()->status);
    }

    public function test_edit_obat_oleh_farmasi_baru_diterapkan_setelah_disetujui(): void
    {
        $farmasi = $this->user('Farmasi');
        $verifikator = $this->user('Verifikator');
        $obat = $this->obat();

        $this->actingAs($farmasi)->postJson('/farmasi/obat/update-data-obat/'.$obat->id, [
            'nama_obat' => 'Obat Setelah Pengajuan',
            'harga_jual_obat' => 3000,
        ])->assertOk();

        $this->assertSame('Obat Uji', $obat->fresh()->nama_obat);
        $this->assertSame(1500.0, (float) $obat->fresh()->harga_jual_obat);

        $pengajuan = $this->pengajuan('obat', 'edit', $obat->id);

        $this->actingAs($verifikator)
            ->postJson("/verifikator/pengajuan/{$pengajuan->id}/setujui", [])
            ->assertOk();

        $obat->refresh();
        $this->assertSame('Obat Setelah Pengajuan', $obat->nama_obat);
        $this->assertSame(3000.0, (float) $obat->harga_jual_obat);
    }

    public function test_pengajuan_ditolak_tidak_mengubah_data(): void
    {
        $admin = $this->user('Admin');
        $verifikator = $this->user('Verifikator');
        $layanan = $this->layanan();

        $this->actingAs($admin)->postJson('/layanan/update-data-layanan', [
            'id' => $layanan->id,
            'kategori_layanan_id' => $layanan->kategori_layanan_id,
            'nama_layanan' => 'Tidak Boleh Berubah',
            'harga_sebelum_diskon' => 50000,
            'is_global' => 1,
        ])->assertOk();

        $pengajuan = $this->pengajuan('layanan', 'edit', $layanan->id);

        $this->actingAs($verifikator)
            ->postJson("/verifikator/pengajuan/{$pengajuan->id}/tolak", [
                'catatan' => 'Harga belum sesuai.',
            ])
            ->assertOk();

        $this->assertSame('Layanan Uji', $layanan->fresh()->nama_layanan);
        $this->assertSame('ditolak', $pengajuan->fresh()->status);
        $this->assertSame('Harga belum sesuai.', $pengajuan->fresh()->catatan_verifikator);
    }

    public function test_hanya_verifikator_yang_bisa_mengakses_halaman_pengajuan(): void
    {
        $this->actingAs($this->user('Admin'))
            ->get('/verifikator')
            ->assertForbidden();

        $this->actingAs($this->user('Verifikator'))
            ->get('/verifikator')
            ->assertOk();
    }

    public function test_data_tabel_pengajuan_tidak_membocorkan_detail_akun_pengaju(): void
    {
        $admin = $this->user('Admin');
        $verifikator = $this->user('Verifikator');
        $layanan = $this->layanan();

        $this->actingAs($admin)->postJson('/layanan/update-data-layanan', [
            'id' => $layanan->id,
            'kategori_layanan_id' => $layanan->kategori_layanan_id,
            'nama_layanan' => 'Layanan Untuk Uji Data Tabel',
            'harga_sebelum_diskon' => 15000,
            'is_global' => 1,
        ])->assertOk();

        $response = $this->actingAs($verifikator)
            ->getJson('/verifikator/pengajuan/data?draw=1&start=0&length=10');

        $response->assertOk();

        $row = $response->json('data.0');

        $this->assertIsArray($row);
        $this->assertArrayNotHasKey('pengaju', $row);
        $this->assertArrayNotHasKey('diajukan_oleh', $row);
        $this->assertSame('Admin', $row['pengaju_role']);
        $this->assertStringNotContainsString($admin->email, $response->getContent());
    }
}
