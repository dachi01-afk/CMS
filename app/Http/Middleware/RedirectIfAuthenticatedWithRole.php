<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticatedWithRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user) {
            // Jika sudah login, arahkan sesuai role
            switch ($user->role) {
                case 'Admin':
                    return redirect()->route('admin.index');
                case 'Farmasi':
                    return redirect()->route('farmasi.dashboard');
                default:
                    // Kalau role tidak dikenali, logout dan arahkan ke login
                    Auth::logout();
                    return redirect()->route('login')->withErrors([
                        'role' => 'Role pengguna tidak dikenali.',
                    ]);
            }
        }

        // Jika belum login, lanjutkan ke halaman login
        return redirect()->route('login');
    }
}
