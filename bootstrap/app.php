<?php

use App\Http\Middleware\IsSuperAdmin;
use App\Http\Middleware\RedirectIfAuthenticatedWithRole;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\TerakhirLogin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ✅ AKTIFKAN CORS GLOBAL (TANPA ARRAY)
        $middleware->append(HandleCors::class);

        // ✅ ALIAS MIDDLEWARE KUSTOM
        $middleware->alias([
            'role'      => RoleMiddleware::class,
            'checkAuth' => RedirectIfAuthenticatedWithRole::class,
            'terakhirLogin' => TerakhirLogin::class,
            'superAdmin' => IsSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
