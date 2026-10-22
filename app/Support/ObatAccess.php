<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Aturan akses data obat (master obat + stok manual).
 *
 * - Super Admin boleh mengubah data obat secara langsung: edit, hapus,
 *   import Excel yang menimpa data lama, perbaikan/opname stok, dan
 *   perubahan stok manual.
 * - Farmasi boleh mengajukan edit/hapus data obat. Pengajuan tidak langsung
 *   mengubah data; Verifikator harus menyetujuinya terlebih dahulu.
 * - Stok tetap bergerak lewat transaksi resmi (penjualan, restock, retur).
 */
class ObatAccess
{
    /** Satu-satunya role yang boleh mengubah data obat secara langsung. */
    public const MANAGER_ROLE = 'super admin';

    /** Role yang boleh mengajukan perubahan data obat. */
    public const REQUESTER_ROLE = 'farmasi';

    public const LOCK_TITLE = 'Hanya Super Admin yang dapat mengubah data obat';

    public static function canManage(?string $role = null): bool
    {
        $role ??= (string) (Auth::user()->role ?? '');

        return strtolower(trim($role)) === self::MANAGER_ROLE;
    }

    public static function canRequestChange(?string $role = null): bool
    {
        $role ??= (string) (Auth::user()->role ?? '');

        return strtolower(trim($role)) === self::REQUESTER_ROLE;
    }
}
