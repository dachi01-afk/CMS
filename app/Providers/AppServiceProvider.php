<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));
        setlocale(LC_TIME, 'id_ID.UTF-8');

        Blade::if(
            'superAdmin',
            fn() =>
            Auth::check() && Auth::user()->role === 'Super Admin'
        );

        Blade::if(
            'auditor',
            fn() =>
            Auth::check() && Auth::user()->role === 'Auditor'
        );

        // Catatan: listener audit autentikasi (app/Listeners/RecordAuthAudit.php)
        // TIDAK didaftarkan manual di sini. Laravel sudah melakukan event
        // discovery untuk seluruh kelas di app/Listeners (pola yang sama dengan
        // KirimNotifikasiHasilLab). Mendaftarkannya manual membuat listener
        // terdaftar dua kali sehingga satu aktivitas tercatat dua kali.
    }
}
