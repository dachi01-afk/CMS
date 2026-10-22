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
 * Perubahan langsung data obat — import Excel yang menimpa data lama dan
 * perubahan stok manual — hanya boleh dilakukan Super Admin. Role lain
 * (termasuk Farmasi) ditolak 403 bila memanggil endpoint tersebut langsung.
 *
 * Endpoint edit/hapus obat tetap dapat dipanggil Farmasi, tetapi controller
 * tidak mengubah data. Farmasi hanya membuat pengajuan yang harus disetujui
 * role Verifikator terlebih dahulu.
 *
 * Yang masih berjalan normal untuk Farmasi: menambah obat baru, serta
 * pergerakan stok lewat transaksi resmi (penjualan, restock, retur).
 */
class RestrictObatWriteAccess
{
    /** Route yang mengubah data obat secara langsung: hanya Super Admin. */
    protected const SUPER_ADMIN_ONLY_ROUTES = [
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
