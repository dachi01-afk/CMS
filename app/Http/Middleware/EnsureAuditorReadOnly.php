<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    /** Route login aplikasi mobile: auditor hanya boleh login lewat web. */
    protected const API_LOGIN_ROUTES = ['api.login', 'api.login_dokter'];

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->route()?->getName(), self::API_LOGIN_ROUTES, true)) {
            return $this->guardApiLogin($request, $next);
        }

        $user = $this->resolveUser($request);

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

        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Auditor hanya memiliki akses baca (read-only).',
            ], 403);
        }

        abort(403, 'Akses ditolak. Auditor hanya memiliki akses baca (read-only) dan tidak dapat mengubah data.');
    }

    protected function resolveUser(Request $request)
    {
        $user = $request->user();

        if (! $user && $request->is('api/*')) {
            $user = Auth::guard('sanctum')->user();
        }

        return $user;
    }

    /**
     * Login mobile berhasil untuk akun Auditor → token langsung dicabut.
     * Auditor bekerja lewat halaman web yang memang dibuat read-only.
     */
    protected function guardApiLogin(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $user = User::where('username', (string) $request->input('username'))->first();

        if (! $user || $user->role !== 'Auditor') {
            return $response;
        }

        // Dicatat sebagai login gagal oleh RecordAuditableRequest.
        $user->tokens()->delete();

        return response()->json([
            'success' => false,
            'message' => 'Akun Auditor hanya dapat digunakan melalui aplikasi web.',
        ], 403);
    }
}
