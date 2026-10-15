<?php

namespace App\Models;

use App\Support\Audit\AuditLogBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Audit log bersifat append-only.
 *
 * Model ini sengaja tidak menyediakan update/delete: setiap percobaan
 * mengubah atau menghapus log akan melempar LogicException, termasuk untuk
 * role Super Admin. Karena itu juga tidak ada kolom updated_at.
 */
class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $guarded = [];

    /** Log hanya punya created_at. */
    public const UPDATED_AT = null;

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (): bool {
            throw new LogicException('Audit log bersifat append-only dan tidak dapat diubah.');
        });

        static::deleting(function (): bool {
            throw new LogicException('Audit log bersifat append-only dan tidak dapat dihapus.');
        });
    }

    /**
     * Builder khusus: menutup jalur mass update/delete yang tidak memicu
     * model event (mis. `AuditLog::where(...)->delete()`).
     */
    public function newEloquentBuilder($query): AuditLogBuilder
    {
        return new AuditLogBuilder($query);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Entitas yang menjadi objek log (pasien, emr, resep, dst). */
    public function auditable()
    {
        return $this->morphTo(__FUNCTION__, 'auditable_type', 'auditable_id');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['from'] ?? null, fn (Builder $q, $from) => $q->where('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $q, $to) => $q->where('created_at', '<=', $to))
            ->when($filters['user_id'] ?? null, fn (Builder $q, $userId) => $q->where('user_id', $userId))
            ->when($filters['role'] ?? null, fn (Builder $q, $role) => $q->where('user_role', $role))
            ->when($filters['module'] ?? null, fn (Builder $q, $module) => $q->where('module', $module))
            ->when($filters['action'] ?? null, fn (Builder $q, $action) => $q->where('action', $action))
            ->when($filters['keyword'] ?? null, function (Builder $q, string $keyword) {
                $keyword = trim($keyword);

                $q->where(function (Builder $sub) use ($keyword) {
                    $sub->where('description', 'like', "%{$keyword}%")
                        ->orWhere('user_name', 'like', "%{$keyword}%")
                        ->orWhere('user_role', 'like', "%{$keyword}%")
                        ->orWhere('module', 'like', "%{$keyword}%")
                        ->orWhere('action', 'like', "%{$keyword}%")
                        ->orWhere('auditable_type', 'like', "%{$keyword}%");
                });
            });
    }

    public function scopeForEntity(Builder $query, ?string $type, $id): Builder
    {
        return $query
            ->when($type, fn (Builder $q, $type) => $q->where('auditable_type', $type))
            ->when($id, fn (Builder $q, $id) => $q->where('auditable_id', $id));
    }

    /** Nama modul dari kelas model, mis. App\Models\Pasien → Pasien. */
    public function getAuditableLabelAttribute(): ?string
    {
        if (! $this->auditable_type) {
            return null;
        }

        return class_basename($this->auditable_type);
    }
}
