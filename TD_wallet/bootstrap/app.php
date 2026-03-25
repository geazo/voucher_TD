<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(function (Request $request) {
            // 1. Jika URL yang sedang diakses berawalan /operator atau /operator/...
            if ($request->is('operator') || $request->is('operator/*')) {
                return route('login'); // Lempar ke login kasir/admin
            }
            // 2. Jika request berupa API/AJAX (Opsional, agar tidak error HTML saat fetch data)
            if ($request->expectsJson()) {
                return null;
            }
            // 3. Sisanya (Default): Lempar ke halaman login Customer
            return route('customer.login');

        });
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
