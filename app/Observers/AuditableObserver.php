<?php

namespace App\Observers;

use App\Support\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Observer generik untuk semua model yang memakai trait Auditable.
 *
 * Tidak ada logika bisnis yang disentuh: observer hanya membaca perubahan
 * model lalu menuliskannya ke audit log.
 */
class AuditableObserver
{
    /** Peta model → nama modul di audit log. */
    protected const MODULES = [
        'App\Models\Pasien' => 'Pasien',
        'App\Models\EMR' => 'Rekam Medis',
        'App\Models\Resep' => 'Resep',
        'App\Models\ResepObat' => 'Resep',
        'App\Models\Obat' => 'Obat',
        'App\Models\BatchObat' => 'Stok Obat',
        'App\Models\BatchObatDepot' => 'Stok Obat',
        'App\Models\DepotObat' => 'Stok Obat',
        'App\Models\MutasiStokObat' => 'Stok Obat',
        'App\Models\RestockObat' => 'Stok Obat',
        'App\Models\ReturnObat' => 'Stok Obat',
        'App\Models\Pembayaran' => 'Pembayaran',
        'App\Models\PembayaranDetail' => 'Pembayaran',
        'App\Models\PenjualanObat' => 'Pembayaran',
        'App\Models\PenjualanLayanan' => 'Pembayaran',
        'App\Models\User' => 'Manajemen User',
        'App\Models\Auditor' => 'Manajemen User',
    ];

    public static function moduleFor(string $modelClass): string
    {
        return self::MODULES[$modelClass] ?? class_basename($modelClass);
    }

    public function created(Model $model): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        AuditLogger::record('create', $model->auditModule(), $this->describe('create', $model), [
            'auditable' => $model,
            'new_values' => $this->attributesFor($model),
        ]);
    }

    public function updated(Model $model): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $changedKeys = $this->changedKeys($model);

        // Tidak ada field yang benar-benar berubah (mis. hanya updated_at).
        if ($changedKeys === []) {
            return;
        }

        $old = [];
        $new = [];

        foreach ($changedKeys as $key) {
            $old[$key] = $model->getOriginal($key);
            $new[$key] = $model->getAttribute($key);
        }

        AuditLogger::record('update', $model->auditModule(), $this->describe('update', $model, $changedKeys), [
            'auditable' => $model,
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }

    public function deleted(Model $model): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        AuditLogger::record('delete', $model->auditModule(), $this->describe('delete', $model), [
            'auditable' => $model,
            'old_values' => $this->attributesFor($model),
        ]);
    }

    /**
     * Hanya catat ketika ada user yang login.
     *
     * Aktivitas seeder/artisan tanpa sesi login tidak perlu menghasilkan log,
     * supaya data contoh tidak membanjiri audit trail.
     */
    protected function shouldLog(): bool
    {
        return ! (app()->runningInConsole() && ! Auth::check());
    }

    /** Field yang benar-benar berubah, tanpa kolom teknis. */
    protected function changedKeys(Model $model): array
    {
        $ignored = $model->auditIgnoredAttributes();

        return array_values(array_diff(array_keys($model->getChanges()), $ignored));
    }

    /** Atribut model yang layak dicatat (sudah dibersihkan dari kolom teknis). */
    protected function attributesFor(Model $model): array
    {
        $ignored = $model->auditIgnoredAttributes();

        return array_diff_key($model->getAttributes(), array_flip($ignored));
    }

    /** Kalimat yang mudah dibaca manusia. */
    protected function describe(string $action, Model $model, array $changedKeys = []): string
    {
        $verb = match ($action) {
            'create' => 'Menambah',
            'update' => 'Mengubah',
            'delete' => 'Menghapus',
            default => 'Aktivitas pada',
        };

        $text = sprintf('%s data %s', $verb, $model->auditModule());

        $useOriginal = in_array($action, ['update', 'delete'], true);

        if ($label = $model->auditLabel($useOriginal)) {
            $text .= ' ('.$label.')';
        }

        $text .= '.';

        if ($action === 'update' && $changedKeys !== []) {
            $text .= ' Field berubah: '.implode(', ', $changedKeys).'.';
        }

        return $text;
    }
}
