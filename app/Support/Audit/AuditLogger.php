<?php

namespace App\Support\Audit;

use App\Models\AuditLog;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonSerializable;
use Throwable;

/**
 * Penulis audit log.
 *
 * Semua penulisan log lewat kelas ini supaya:
 *  - field sensitif selalu disaring,
 *  - identitas pelaku selalu di-snapshot,
 *  - kegagalan menulis log TIDAK pernah menggagalkan proses utama
 *    (cukup dicatat ke error log aplikasi).
 */
class AuditLogger
{
    /**
     * Tulis satu baris audit log.
     *
     * @param  array{
     *     auditable?: Model|null,
     *     old_values?: array|null,
     *     new_values?: array|null,
     *     user?: \App\Models\User|null,
     *     user_name?: string|null,
     *     user_role?: string|null
     * }  $context
     */
    public static function record(string $action, string $module, ?string $description = null, array $context = []): ?AuditLog
    {
        try {
            $request = self::currentRequest();
            $user = $context['user'] ?? Auth::user();
            $auditable = $context['auditable'] ?? null;

            [$type, $id] = self::resolveAuditable($auditable);

            $pasienId = array_key_exists('pasien_id', $context)
                ? $context['pasien_id']
                : ($auditable instanceof Model ? PasienResolver::forModel($auditable) : null);

            return AuditLog::create([
                'user_id' => $user?->getKey(),
                'user_name' => $context['user_name'] ?? self::resolveUserName($user),
                'user_role' => $context['user_role'] ?? $user?->role,
                'action' => $action,
                'module' => $module,
                'auditable_type' => $type,
                'auditable_id' => $id,
                'old_values' => self::sanitize($context['old_values'] ?? null),
                'new_values' => self::sanitize($context['new_values'] ?? null),
                'description' => $description,
                'ip_address' => $request?->ip(),
                'user_agent' => $request ? Str::limit((string) $request->userAgent(), 1000, '') : null,
                'url' => $request ? Str::limit($request->fullUrl(), 2000, '') : null,
                'http_method' => $request?->method(),
                'pasien_id' => $pasienId,
                'source' => $context['source'] ?? self::currentSource($request),
            ]);
        } catch (Throwable $e) {
            // Kegagalan audit log tidak boleh menggagalkan permintaan user.
            Log::error('Gagal menulis audit log', [
                'action' => $action,
                'module' => $module,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Buang field rahasia dari nilai yang akan disimpan.
     */
    public static function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $excluded = config('audit.excluded_fields', []);
        $clean = [];

        foreach ($values as $key => $value) {
            if (self::isExcluded((string) $key, $excluded)) {
                continue;
            }

            $clean[$key] = self::normalize($value);
        }

        return $clean === [] ? null : $clean;
    }

    /** Apakah nama kolom/field termasuk rahasia (password, token, OTP, ...). */
    public static function isSensitiveField(string $key): bool
    {
        return self::isExcluded($key, config('audit.excluded_fields', []));
    }

    /**
     * Cocokkan nama field dengan daftar yang dikecualikan.
     * Dicocokkan sebagai nama utuh, awalan (password_confirmation), atau
     * akhiran (fcm_token, current_password) supaya tidak salah tangkap.
     */
    protected static function isExcluded(string $key, array $excluded): bool
    {
        $key = Str::lower(trim($key));

        foreach ($excluded as $needle) {
            $needle = Str::lower($needle);

            if ($key === $needle || Str::startsWith($key, $needle.'_') || Str::endsWith($key, '_'.$needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ubah nilai apa pun menjadi bentuk yang aman disimpan di JSON.
     */
    protected static function normalize(mixed $value): mixed
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        } elseif ($value instanceof JsonSerializable) {
            $value = $value->jsonSerialize();
        }

        if (is_array($value)) {
            return array_map(fn ($item) => self::normalize($item), $value);
        }

        if (is_object($value)) {
            return method_exists($value, '__toString') ? (string) $value : get_class($value);
        }

        return $value;
    }

    /**
     * @return array{0: string|null, 1: mixed}
     */
    protected static function resolveAuditable(?Model $auditable): array
    {
        if (! $auditable instanceof Model) {
            return [null, null];
        }

        return [$auditable->getMorphClass(), $auditable->getKey()];
    }

    protected static function resolveUserName($user): ?string
    {
        if (! $user) {
            return null;
        }

        return $user->username ?? $user->name ?? $user->email ?? null;
    }

    protected static function currentRequest(): mixed
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return null;
        }

        return app()->bound('request') ? request() : null;
    }

    /** web, mobile (API), atau sistem (artisan / antrian / tanpa request). */
    protected static function currentSource($request): string
    {
        if ($request && $request->route()) {
            return $request->is('api/*') ? 'mobile' : 'web';
        }

        return AuditContext::source();
    }
}
