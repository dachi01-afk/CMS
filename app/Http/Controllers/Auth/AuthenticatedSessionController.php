<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        switch ($user->role) {
            case 'Admin':
                return redirect()->route('admin.index');
            case 'Farmasi':
                return redirect()->route('farmasi.dashboard');
            case 'Perawat':
                return redirect()->route('perawat.dashboard');
            case 'Kasir':
                return redirect()->route('kasir.dashboard');
            default:
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'role' => 'Role pengguna tidak dikenali.',
                ]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
