<?php

namespace App\Listeners;

use App\Support\Audit\AuditContext;
use App\Support\Audit\AuditTriggerInstaller;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Menjaga trigger audit dan konteks user tetap sinkron dengan aplikasi.
 *
 * Didaftarkan otomatis lewat event auto-discovery Laravel (sama seperti
 * listener lain di folder ini), jadi tidak perlu registrasi manual.
 */
class AuditDatabaseLifecycle
{
    /** Perintah artisan yang tidak perlu menghasilkan audit log. */
    protected const SILENT_COMMANDS = [
        'migrate', 'migrate:fresh', 'migrate:refresh', 'migrate:reset', 'migrate:rollback',
        'db:seed', 'db:wipe', 'schema:dump', 'audit:triggers',
    ];

    protected static bool $installPending = false;

    /** Koneksi database baru dibuka: kirim konteks audit yang sedang berlaku. */
    public function handleConnectionEstablished(ConnectionEstablished $event): void
    {
        AuditContext::apply($event->connection);
    }

    /** User teridentifikasi (session, actingAs, atau login): catat sebagai pelaku. */
    public function handleAuthenticated(Authenticated $event): void
    {
        $current = AuditContext::user();

        if (($current['id'] ?? null) !== $event->user?->getKey()) {
            AuditContext::setUser($event->user);
        }
    }

    public function handleLogin(Login $event): void
    {
        AuditContext::setUser($event->user);
    }

    public function handleCommandStarting(CommandStarting $event): void
    {
        AuditContext::startSystem(in_array($event->command, self::SILENT_COMMANDS, true));
    }

    /**
     * Job di worker antrian mulai dengan konteks bersih atas nama "Sistem".
     * Job 'sync' berjalan di dalam request user, jadi pelakunya tetap user itu.
     */
    public function handleJobProcessing(JobProcessing $event): void
    {
        if ($event->connectionName === 'sync') {
            return;
        }

        AuditContext::startSystem();
    }

    /**
     * Sebelum migrasi: lepas trigger supaya perubahan struktur tabel (hapus /
     * ganti nama kolom) tidak bertabrakan dengan trigger lama.
     */
    public function handleMigrationsStarted(MigrationsStarted $event): void
    {
        try {
            if (! AuditTriggerInstaller::supported()) {
                return;
            }

            AuditContext::disable();
            (new AuditTriggerInstaller)->drop();
            static::$installPending = true;

            // Bila migrasi gagal di tengah jalan, trigger tetap dipasang kembali.
            register_shutdown_function(function () {
                if (static::$installPending) {
                    $this->install();
                }
            });
        } catch (Throwable $e) {
            $this->report('Gagal melepas trigger audit sebelum migrasi', $e);
        }
    }

    /** Sesudah migrasi: bangun ulang trigger dari struktur tabel terbaru. */
    public function handleMigrationsEnded(MigrationsEnded $event): void
    {
        $this->install();
    }

    protected function install(): void
    {
        static::$installPending = false;

        try {
            if (! AuditTriggerInstaller::supported()) {
                return;
            }

            $result = (new AuditTriggerInstaller)->install();

            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                fwrite(STDOUT, sprintf(
                    "\n  Audit log: %d trigger terpasang di %d tabel.\n",
                    $result['triggers'],
                    count($result['tables'])
                ));
            }
        } catch (Throwable $e) {
            $this->report('Gagal memasang trigger audit. Perubahan data TIDAK tercatat sampai trigger terpasang (jalankan: php artisan audit:triggers)', $e);
        }
    }

    protected function report(string $message, Throwable $e): void
    {
        Log::error($message, ['error' => $e->getMessage()]);

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            fwrite(STDERR, "\n  [AUDIT] {$message}\n  {$e->getMessage()}\n");
        }
    }
}
