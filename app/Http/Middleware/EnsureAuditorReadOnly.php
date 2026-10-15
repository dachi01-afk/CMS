<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Penjaga read-only untuk role Auditor.
 *
 * Dijalankan pada seluruh request web (bukan hanya halaman auditor), supaya
 * meski auditor menemukan endpoint tulis secara langsung — atau memalsukan
 * request — aksesnya tetap ditolak di backend.
 */
class EnsureAuditorReadOnly
{
    /** Method yang aman karena tidak mengubah data. */
    protected const READ_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Bukan auditor: tidak diintervensi.
        if (! $user || $user->role !== 'Auditor') {
            return $next($request);
        }

        if (in_array($request->method(), self::READ_METHODS, true)) {
            return $next($request);
        }

        // Pengecualian terbatas: logout & konfirmasi password tidak mengubah data.
        $routeName = $request->route()?->getName();
        $allowed = config('audit.auditor_allowed_write_routes', []);

        if ($routeName && in_array($routeName, $allowed, true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Auditor hanya memiliki akses baca (read-only).',
            ], 403);
        }

        abort(403, 'Akses ditolak. Auditor hanya memiliki akses baca (read-only) dan tidak dapat mengubah data.');
    }
}
