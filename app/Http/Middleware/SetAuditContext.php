<?php

namespace App\Http\Middleware;

use App\Support\Audit\AuditContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Memberi tahu database siapa yang sedang beraksi pada request ini.
 *
 * Dipasang di grup web dan api. Trigger audit memakai informasi ini untuk
 * mengisi kolom user, IP, URL, dan sumber (web / mobile) pada setiap
 * perubahan data, termasuk yang ditulis lewat raw query.
 */
class SetAuditContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $isApi = $request->is('api/*');

        AuditContext::startRequest($request, $isApi ? 'mobile' : 'web');

        try {
            $user = $request->user() ?? ($isApi ? Auth::guard('sanctum')->user() : null);

            if ($user) {
                AuditContext::setUser($user);
            }
        } catch (Throwable) {
            // Token/sesi tidak valid: biarkan middleware auth yang menolak.
        }

        return $next($request);
    }
}
