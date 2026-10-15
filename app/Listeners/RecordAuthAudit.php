<?php

namespace App\Listeners;

use App\Support\Audit\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

/**
 * Mencatat aktivitas autentikasi lewat event bawaan Laravel, bukan dengan
 * menambah kode di controller login.
 */
class RecordAuthAudit
{
    public function handleLogin(Login $event): void
    {
        AuditLogger::record('login', 'Autentikasi', 'Login berhasil.', [
            'user' => $event->user,
        ]);
    }

    /**
     * Kredensial sengaja TIDAK diambil dari event ini karena berisi password.
     * Hanya identitas login yang dipakai sebagai keterangan.
     */
    public function handleFailed(Failed $event): void
    {
        $credentials = (array) $event->credentials;
        $identity = $credentials['email'] ?? $credentials['username'] ?? null;

        AuditLogger::record('login_failed', 'Autentikasi', 'Login gagal (kredensial tidak valid).', [
            'user' => $event->user,
            'user_name' => $event->user?->username ?? $identity,
            'user_role' => $event->user?->role,
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        AuditLogger::record('logout', 'Autentikasi', 'Logout dari sistem.', [
            'user' => $event->user,
        ]);
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        AuditLogger::record('reset_password', 'Autentikasi', 'Password direset melalui fitur lupa password.', [
            'user' => $event->user,
            // Nilai password baru tidak pernah ikut dicatat.
            'old_values' => null,
            'new_values' => null,
        ]);
    }
}
