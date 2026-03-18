<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ... $roles): Response
    {
        if (!Auth::check()) {
            return redirect('/login'); // Redirect ke halaman login jika belum login
        }

        // Cek apakah role user saat ini ada di dalam daftar role yang diizinkan
        if (in_array(Auth::user()->role, $roles)) {
            return $next($request);
        }

        // Jika tidak punya akses, tampilkan error 403 Forbidden
        abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk membuka halaman ini.');
    }
}
