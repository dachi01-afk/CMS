<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || strtolower(trim($user->role)) !== 'super admin') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.',
                ], 403);
            }

            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengakses fitur ini.');
        }

        return $next($request);
    }
}
