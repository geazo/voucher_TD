<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customer = Auth::guard('customer')->user();

        // Jika customer sedang login DAN passwordnya masih "password" (default), maka paksa mereka untuk ganti password
        if ($customer && Hash::check('password', $customer->password)) {

            // Izinkan mereka mengakses route ganti password agar tidak terjadi redirect loop berulang-ulang
            if ($request->routeIs('customer.password.change') || $request->routeIs('customer.password.update') || $request->routeIs('customer.logout')) {
                return $next($request);
            }

            // Jika mencoba akses halaman lain, paksa redirect ke halaman ganti password
            return redirect()->route('customer.password.change')
                ->with('warning', 'Demi keamanan, Anda wajib mengganti password default sebelum melanjutkan.');
        }

        return $next($request);
    }
}
