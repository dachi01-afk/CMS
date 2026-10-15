<?php

namespace App\Support\Audit;

use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Identitas "siapa sedang melakukan apa" untuk trigger audit di MySQL.
 *
 * Trigger database tidak tahu siapa user yang login. Kelas ini menyimpan
 * identitas user, IP, URL, dan sumber permintaan (web / mobile / sistem),
 * lalu mengirimnya ke MySQL sebagai variabel sesi (@audit_user_id, dst).
 * Setiap baris yang diubah selama request itu, lewat jalur apa pun, akan
 * tercatat atas nama user tersebut.
 *
 * Variabel dikirim ulang setiap kali koneksi database dibuat, sehingga aman
 * untuk koneksi yang baru dibuka belakangan (lazy) maupun koneksi ulang.
 */
class AuditContext
{
    /** @var array<string, mixed> */
    protected static array $values = [];

    /** Identitas user yang sedang aktif (bukan model, supaya ringan). */
    protected static ?array $user = null;

    /** Daftar variabel yang dikenal trigger. */
    public const VARIABLES = [
        'audit_user_id',
        'audit_user_name',
        'audit_user_role',
        'audit_ip',
        'audit_user_agent',
        'audit_url',
        'audit_method',
        'audit_source',
        'audit_tz',
        'audit_disabled',
    ];

    /** Mulai konteks untuk satu permintaan HTTP. */
    public static function startRequest(Request $request, string $source): void
    {
        static::$values = [
            'audit_ip' => $request->ip(),
            'audit_user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'audit_url' => Str::limit($request->fullUrl(), 2000, ''),
            'audit_method' => $request->method(),
            'audit_source' => $source,
            'audit_disabled' => 0,
        ];

        static::$user = null;
        static::apply();
    }

    /** Konteks untuk proses tanpa request: artisan, scheduler, antrian. */
    public static function startSystem(bool $disabled = false): void
    {
        static::$values = [
            'audit_source' => 'sistem',
            'audit_disabled' => $disabled ? 1 : 0,
        ];

        static::$user = null;
        static::apply();
    }

    /** Matikan pencatatan trigger sementara (dipakai saat migrate & seeding). */
    public static function disable(): void
    {
        static::$values['audit_disabled'] = 1;
        static::apply();
    }

    public static function enable(): void
    {
        static::$values['audit_disabled'] = 0;
        static::apply();
    }

    /** Tetapkan user yang sedang melakukan aksi. */
    public static function setUser($user): void
    {
        static::$user = $user ? [
            'id' => $user->getKey(),
            'name' => $user->username ?? $user->name ?? $user->email ?? null,
            'role' => $user->role ?? null,
        ] : null;

        static::apply();
    }

    public static function user(): ?array
    {
        return static::$user;
    }

    public static function source(): string
    {
        return static::$values['audit_source'] ?? 'sistem';
    }

    /** Hapus semua konteks (dipakai di test & antrian). */
    public static function flush(): void
    {
        static::$values = [];
        static::$user = null;
        static::apply();
    }

    /** @return array<string, mixed> */
    public static function variables(): array
    {
        $values = static::$values;

        $values['audit_user_id'] = static::$user['id'] ?? null;
        $values['audit_user_name'] = static::$user['name'] ?? null;
        $values['audit_user_role'] = static::$user['role'] ?? null;
        $values['audit_tz'] = now()->format('P');

        $ordered = [];

        foreach (self::VARIABLES as $name) {
            $ordered[$name] = $values[$name] ?? null;
        }

        return $ordered;
    }

    /**
     * Kirim variabel ke koneksi MySQL.
     *
     * Tanpa $connection, hanya dikirim bila koneksi default sudah benar-benar
     * terbuka. Koneksi yang belum terbuka akan menerima variabel lewat event
     * ConnectionEstablished (lihat AuditDatabaseLifecycle).
     */
    public static function apply(?Connection $connection = null): void
    {
        if (! static::enabledForDriver()) {
            return;
        }

        try {
            $connection ??= static::openDefaultConnection();

            if (! $connection || $connection->getDriverName() !== 'mysql') {
                return;
            }

            $variables = static::variables();

            $assignments = implode(', ', array_map(fn ($name) => '@'.$name.' = ?', array_keys($variables)));

            $connection->statement('SET '.$assignments, array_values($variables));
        } catch (Throwable $e) {
            Log::error('Gagal mengirim konteks audit ke database', ['error' => $e->getMessage()]);
        }
    }

    protected static function enabledForDriver(): bool
    {
        return config('audit.capture', 'trigger') === 'trigger';
    }

    /** Koneksi default HANYA bila sudah dibuat, supaya tidak membuka koneksi baru. */
    protected static function openDefaultConnection(): ?Connection
    {
        $name = DB::getDefaultConnection();
        $connections = DB::getConnections();

        return $connections[$name] ?? null;
    }
}
