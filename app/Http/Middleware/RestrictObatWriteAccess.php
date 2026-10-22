<?php

namespace App\Http\Middleware;

use App\Support\ObatAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Penjaga khusus data obat (master obat di menu Farmasi).
 *
 * Hanya Super Admin yang boleh mengubah data obat — termasuk mengedit,
 * menghapus, menimpa lewat import Excel, dan mengubah stok secara manual.
 * Role lain (termasuk Farmasi sebagai pemilik menu obat) ditolak 403 bila
 * memanggil endpoint tersebut secara langsung. Tombol Edit/Hapus dan Import
 * tetap tampil, tetapi dalam keadaan nonaktif.
 *
 * Yang masih berjalan normal untuk Farmasi: menambah obat baru, serta
 * pergerakan stok lewat transaksi resmi (penjualan, restock, retur).
 * Fitur lain di luar data obat tidak disentuh middleware ini.
 */
class RestrictObatWriteAccess
{
    /** Route yang mengubah data obat: hanya boleh diakses Super Admin. */
    protected const SUPER_ADMIN_ONLY_ROUTES = [
        'obat.update',
        'obat.delete',
        'import.data.obat',
        'penjualan_obat.update_stok',
        'repair.stok.obat',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $role = strtolower(trim((string) (($request->user() ?? Auth::user())?->role ?? '')));

        if (ObatAccess::canManage($role)) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, self::SUPER_ADMIN_ONLY_ROUTES, true)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Super Admin yang dapat mengubah data obat.',
                ], 403);
            }

            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengubah data obat.');
        }

        return $next($request);
    }
}
