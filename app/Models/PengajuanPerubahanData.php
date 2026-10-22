<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengajuan perubahan data dari Admin (layanan) dan Farmasi (obat).
 *
 * Pengajuan TIDAK langsung mengubah data. Verifikator harus menyetujui
 * terlebih dahulu, baru perubahan diterapkan ke data aslinya.
 */
class PengajuanPerubahanData extends Model
{
    public const JENIS_LAYANAN = 'layanan';
    public const JENIS_OBAT = 'obat';

    public const AKSI_EDIT = 'edit';
    public const AKSI_HAPUS = 'hapus';

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_DITOLAK = 'ditolak';

    protected $table = 'pengajuan_perubahan_data';

    protected $guarded = [];

    protected $appends = [
        'jenis_data_label',
        'aksi_label',
        'status_label',
    ];

    protected function casts(): array
    {
        return [
            'data_lama' => 'array',
            'data_baru' => 'array',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function getJenisDataLabelAttribute(): string
    {
        return match ($this->jenis_data) {
            self::JENIS_LAYANAN => 'Layanan',
            self::JENIS_OBAT => 'Obat',
            default => ucfirst((string) $this->jenis_data),
        };
    }

    public function getAksiLabelAttribute(): string
    {
        return match ($this->aksi) {
            self::AKSI_EDIT => 'Edit',
            self::AKSI_HAPUS => 'Hapus',
            default => ucfirst((string) $this->aksi),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU => 'Menunggu',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_DITOLAK => 'Ditolak',
            default => ucfirst((string) $this->status),
        };
    }

    public function isMenunggu(): bool
    {
        return $this->status === self::STATUS_MENUNGGU;
    }

    /**
     * Baris perbandingan data lama vs data baru untuk modal detail.
     *
     * @return array<int, array{label: string, lama: string, baru: ?string}>
     */
    public function detailRows(): array
    {
        $lama = $this->data_lama ?? [];
        $baru = $this->data_baru ?? [];
        $isHapus = $this->aksi === self::AKSI_HAPUS;

        if ($this->jenis_data === self::JENIS_LAYANAN) {
            return [
                [
                    'label' => 'Nama Layanan',
                    'lama' => (string) ($lama['nama_layanan'] ?? '-'),
                    'baru' => $isHapus ? null : (string) ($baru['nama_layanan'] ?? '-'),
                ],
                [
                    'label' => 'Kategori',
                    'lama' => (string) ($lama['kategori_layanan'] ?? '-'),
                    'baru' => $isHapus ? null : (string) ($baru['kategori_layanan'] ?? '-'),
                ],
                [
                    'label' => 'Harga Sebelum Diskon',
                    'lama' => $this->formatRupiah($lama['harga_sebelum_diskon'] ?? 0),
                    'baru' => $isHapus ? null : $this->formatRupiah($baru['harga_sebelum_diskon'] ?? 0),
                ],
                [
                    'label' => 'Cakupan Poli',
                    'lama' => $this->cakupanPoli($lama),
                    'baru' => $isHapus ? null : $this->cakupanPoli($baru),
                ],
            ];
        }

        return [
            [
                'label' => 'Kode Obat',
                'lama' => (string) ($lama['kode_obat'] ?? '-'),
                'baru' => $isHapus ? null : (string) ($baru['kode_obat'] ?? $lama['kode_obat'] ?? '-'),
            ],
            [
                'label' => 'Nama Obat',
                'lama' => (string) ($lama['nama_obat'] ?? '-'),
                'baru' => $isHapus ? null : (string) ($baru['nama_obat'] ?? '-'),
            ],
            [
                'label' => 'Harga Jual',
                'lama' => $this->formatRupiah($lama['harga_jual_obat'] ?? 0),
                'baru' => $isHapus ? null : $this->formatRupiah($baru['harga_jual_obat'] ?? 0),
            ],
        ];
    }

    protected function cakupanPoli(array $data): string
    {
        if ((int) ($data['is_global'] ?? 0) === 1) {
            return 'Semua Poli';
        }

        $poli = array_filter($data['poli'] ?? []);

        return count($poli) > 0 ? implode(', ', $poli) : 'Belum ditentukan';
    }

    protected function formatRupiah($value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }
}
