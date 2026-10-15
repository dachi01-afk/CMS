<?php

namespace App\Support\Audit;

use App\Observers\AuditableObserver;

/**
 * Tempelkan trait ini ke model yang perubahannya harus dicatat otomatis
 * (create/update/delete) tanpa menulis kode di controller.
 *
 * Observer didaftarkan otomatis lewat konvensi boot<TraitName>() milik Eloquent.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::observe(AuditableObserver::class);
    }

    /** Nama modul yang tampil di audit log, mis. "Rekam Medis". */
    public function auditModule(): string
    {
        return AuditableObserver::moduleFor(static::class);
    }

    /**
     * Label manusiawi untuk entitas ini, dipakai di kolom description.
     * Dicoba dari beberapa kolom yang umum ada, lalu fallback ke primary key.
     */
    public function auditLabel(bool $useOriginal = false): ?string
    {
        $candidates = [
            'nama_pasien',
            'nama_obat',
            'nama_auditor',
            'no_emr',
            'kode_transaksi',
            'username',
            'nama_layanan',
            'nama_supplier',
            'nama_depot',
            'no_faktur',
            'kode_resep',
            'nama_batch',
        ];

        foreach ($candidates as $attribute) {
            // Untuk update/delete, label diambil dari nilai sebelum perubahan
            // supaya entitas tetap teridentifikasi walau field labelnya ikut berubah.
            $value = $useOriginal
                ? $this->getRawOriginal($attribute)
                : $this->getAttribute($attribute);

            if (! empty($value) && is_scalar($value)) {
                return (string) $value;
            }
        }

        $key = $this->getKey();

        return $key ? '#'.$key : null;
    }

    /** Kolom yang tidak perlu masuk ke diff old/new values. */
    public function auditIgnoredAttributes(): array
    {
        return array_merge(
            [$this->getKeyName(), 'created_at', 'updated_at', 'updated_by'],
            method_exists($this, 'getHidden') ? $this->getHidden() : [],
            $this->auditExcludedAttributes(),
        );
    }

    /**
     * Kolom tambahan yang tidak perlu dicatat per model.
     *
     * Dipakai untuk kolom yang sering berubah otomatis (mis. terakhir_login
     * yang di-refresh heartbeat tiap 60 detik) agar tidak membanjiri audit log.
     */
    public function auditExcludedAttributes(): array
    {
        return [];
    }
}
