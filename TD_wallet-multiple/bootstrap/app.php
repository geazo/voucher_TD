<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Jangan lupa panggil facade Auth

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // 1. PENGGANTI Authenticate.php
        // Mengatur ke mana user diusir jika BELUM LOGIN tapi nekat akses halaman rahasia
        $middleware->redirectGuestsTo(fn (Request $request) =>
            $request->is('customer*') ? route('customer.login') : route('login')
        );

        // 2. PENGGANTI RedirectIfAuthenticated.php
        // Mengatur ke mana user diarahkan jika SUDAH LOGIN tapi iseng buka halaman login
        $middleware->redirectUsersTo(function (Request $request) {
            // Jika dia adalah Customer, kembalikan ke dashboard customer
            if (Auth::guard('customer')->check()) {
                return route('customer.dashboard');
            }
            // Jika dia adalah Operator/Admin, kembalikan ke dashboard admin
            return route('dashboard');
        });
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
    })->create();
