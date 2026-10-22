<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Aturan akses data obat (master obat + stok manual).
 *
 * Per 2026-10, hanya Super Admin yang boleh mengubah data obat: edit, hapus,
 * import Excel yang menimpa data lama, perbaikan/opname stok, dan perubahan
 * stok manual. Farmasi tetap bisa melihat data dan menambah obat baru, serta
 * stok tetap bergerak lewat transaksi resmi (penjualan, restock, retur).
 */
class ObatAccess
{
    /** Satu-satunya role yang boleh mengubah data obat. */
    public const MANAGER_ROLE = 'super admin';

    public const LOCK_TITLE = 'Hanya Super Admin yang dapat mengubah data obat';

    public static function canManage(?string $role = null): bool
    {
        $role ??= (string) (Auth::user()->role ?? '');

        return strtolower(trim($role)) === self::MANAGER_ROLE;
    }
}
