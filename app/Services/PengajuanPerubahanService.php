<?php

namespace App\Services;

use App\Models\Layanan;
use App\Models\Obat;
use App\Models\PengajuanPerubahanData;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Alur pengajuan perubahan data (edit/hapus) dari Admin dan Farmasi
 * ke role Verifikator.
 *
 * Data asli TIDAK diubah saat pengajuan dibuat. Perubahan baru diterapkan
 * ketika Verifikator menyetujui pengajuan.
 */
class PengajuanPerubahanService
{
    /**
     * Simpan pengajuan baru. Bila sudah ada pengajuan dengan jenis, aksi,
     * dan target yang sama yang masih menunggu, pengajuan lama dikembalikan
     * agar tidak terjadi duplikasi.
     *
     * @return array{0: PengajuanPerubahanData, 1: bool}
     */
    public function ajukan(
        string $jenisData,
        string $aksi,
        int $targetId,
        string $targetLabel,
        array $dataLama,
        ?array $dataBaru,
        User $pengaju
    ): array {
        $pending = PengajuanPerubahanData::query()
            ->where('jenis_data', $jenisData)
            ->where('aksi', $aksi)
            ->where('target_id', $targetId)
            ->where('status', PengajuanPerubahanData::STATUS_MENUNGGU)
            ->first();

        if ($pending) {
            // Pengajuan sebelumnya belum diproses: perbarui isinya dengan
            // data terbaru agar Verifikator melihat usulan paling akhir.
            $pending->update([
                'target_label' => $targetLabel,
                'data_lama' => $dataLama,
                'data_baru' => $dataBaru,
                'diajukan_oleh' => $pengaju->id,
            ]);

            return [$pending->fresh(), false];
        }

        $pengajuan = PengajuanPerubahanData::create([
            'jenis_data' => $jenisData,
            'aksi' => $aksi,
            'target_id' => $targetId,
            'target_label' => $targetLabel,
            'data_lama' => $dataLama,
            'data_baru' => $dataBaru,
            'status' => PengajuanPerubahanData::STATUS_MENUNGGU,
            'diajukan_oleh' => $pengaju->id,
        ]);

        return [$pengajuan, true];
    }

    /**
     * Setujui pengajuan dan terapkan perubahannya ke data asli.
     */
    public function setujui(
        PengajuanPerubahanData $pengajuan,
        User $verifikator,
        ?string $catatan = null
    ): PengajuanPerubahanData {
        return DB::transaction(function () use ($pengajuan, $verifikator, $catatan) {
            $pengajuan = PengajuanPerubahanData::lockForUpdate()->findOrFail($pengajuan->id);

            if (! $pengajuan->isMenunggu()) {
                throw new RuntimeException('Pengajuan ini sudah diproses sebelumnya.');
            }

            if ($pengajuan->jenis_data === PengajuanPerubahanData::JENIS_LAYANAN) {
                $this->terapkanLayanan($pengajuan);
            } elseif ($pengajuan->jenis_data === PengajuanPerubahanData::JENIS_OBAT) {
                $this->terapkanObat($pengajuan);
            } else {
                throw new RuntimeException('Jenis data pengajuan tidak dikenali.');
            }

            $pengajuan->update([
                'status' => PengajuanPerubahanData::STATUS_DISETUJUI,
                'diverifikasi_oleh' => $verifikator->id,
                'diverifikasi_pada' => now(),
                'catatan_verifikator' => $catatan,
            ]);

            return $pengajuan->fresh(['pengaju', 'verifikator']);
        });
    }

    /**
     * Tolak pengajuan tanpa mengubah data asli.
     */
    public function tolak(
        PengajuanPerubahanData $pengajuan,
        User $verifikator,
        ?string $catatan = null
    ): PengajuanPerubahanData {
        return DB::transaction(function () use ($pengajuan, $verifikator, $catatan) {
            $pengajuan = PengajuanPerubahanData::lockForUpdate()->findOrFail($pengajuan->id);

            if (! $pengajuan->isMenunggu()) {
                throw new RuntimeException('Pengajuan ini sudah diproses sebelumnya.');
            }

            $pengajuan->update([
                'status' => PengajuanPerubahanData::STATUS_DITOLAK,
                'diverifikasi_oleh' => $verifikator->id,
                'diverifikasi_pada' => now(),
                'catatan_verifikator' => $catatan,
            ]);

            return $pengajuan->fresh(['pengaju', 'verifikator']);
        });
    }

    /**
     * Snapshot data layanan untuk ditampilkan dan dibandingkan Verifikator.
     */
    public function snapshotLayanan(Layanan $layanan): array
    {
        $layanan->loadMissing(['kategoriLayanan', 'layananPoli']);

        return [
            'nama_layanan' => $layanan->nama_layanan,
            'kategori_layanan_id' => $layanan->kategori_layanan_id,
            'kategori_layanan' => optional($layanan->kategoriLayanan)->nama_kategori,
            'harga_sebelum_diskon' => (float) ($layanan->harga_sebelum_diskon ?? 0),
            'is_global' => (int) ($layanan->is_global ?? 0),
            'poli_id' => $layanan->layananPoli->pluck('id')->values()->all(),
            'poli' => $layanan->layananPoli->pluck('nama_poli')->values()->all(),
        ];
    }

    /**
     * Snapshot data obat yang boleh diubah (nama dan harga jual).
     */
    public function snapshotObat(Obat $obat): array
    {
        return [
            'kode_obat' => $obat->kode_obat,
            'nama_obat' => $obat->nama_obat,
            'harga_jual_obat' => (float) ($obat->harga_jual_obat ?? 0),
        ];
    }

    public function labelLayanan(Layanan $layanan): string
    {
        return trim((string) $layanan->nama_layanan) ?: 'Layanan #'.$layanan->id;
    }

    public function labelObat(Obat $obat): string
    {
        $label = trim((string) $obat->nama_obat) ?: 'Obat #'.$obat->id;

        return $obat->kode_obat ? $obat->kode_obat.' - '.$label : $label;
    }

    /**
     * Terapkan edit/hapus layanan. Logika edit mengikuti alur update layanan
     * yang sudah ada agar hasil persetujuan sama dengan edit langsung.
     */
    protected function terapkanLayanan(PengajuanPerubahanData $pengajuan): void
    {
        $layanan = Layanan::find($pengajuan->target_id);

        if (! $layanan) {
            throw new RuntimeException('Data layanan sudah tidak tersedia. Pengajuan tidak dapat diproses.');
        }

        if ($pengajuan->aksi === PengajuanPerubahanData::AKSI_HAPUS) {
            $layanan->delete();

            return;
        }

        $baru = $pengajuan->data_baru ?? [];

        $layanan->update([
            'kategori_layanan_id' => (int) ($baru['kategori_layanan_id'] ?? $layanan->kategori_layanan_id),
            'nama_layanan' => $baru['nama_layanan'] ?? $layanan->nama_layanan,
            'harga_sebelum_diskon' => (float) ($baru['harga_sebelum_diskon'] ?? $layanan->harga_sebelum_diskon),
        ]);

        if (Schema::hasColumn('layanan', 'is_global')) {
            $layanan->is_global = 1;
            $layanan->save();
        }

        if ((int) ($baru['is_global'] ?? 0) === 0) {
            $layanan->layananPoli()->sync($baru['poli_id'] ?? []);
        } else {
            $layanan->layananPoli()->detach();
        }
    }

    /**
     * Terapkan edit/hapus obat. Sama seperti update obat yang berlaku saat
     * ini, field yang berubah hanya nama obat dan harga jual.
     */
    protected function terapkanObat(PengajuanPerubahanData $pengajuan): void
    {
        $obat = Obat::find($pengajuan->target_id);

        if (! $obat) {
            throw new RuntimeException('Data obat sudah tidak tersedia. Pengajuan tidak dapat diproses.');
        }

        if ($pengajuan->aksi === PengajuanPerubahanData::AKSI_HAPUS) {
            $obat->delete();

            return;
        }

        $baru = $pengajuan->data_baru ?? [];

        $obat->updateDataObat([
            'nama_obat' => $baru['nama_obat'] ?? $obat->nama_obat,
            'harga_jual_obat' => (float) ($baru['harga_jual_obat'] ?? $obat->harga_jual_obat),
        ]);
    }
}
