<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OperatorController extends Controller
{
    // Menampilkan halaman form login
    public function showLoginForm()
    {
        return view('login');
    }

    // Memproses data login
    public function login(Request $request)
    {
        // 1. Validasi input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Coba melakukan autentikasi
        if (Auth::attempt($credentials)) {
            // Jika berhasil, regenerate session untuk keamanan (mencegah session fixation)
            $request->session()->regenerate();

            $user = Auth::user();

            // 3. Redirect berdasarkan Role
            if (in_array($user->role, ['superadmin', 'admin'])) {
                return redirect()->route('dashboard');
            } elseif ($user->role === 'kasir') {
                return redirect()->route('topup.index');
            }

            // Fallback jika role tidak dikenali
            return redirect('/');
        }

        // 4. Jika gagal, kembalikan ke halaman login dengan pesan error
        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan tidak sesuai dengan data kami.',
        ])->onlyInput('email'); // Tetap pertahankan input email yang diketik
    }

    // Memproses logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    // Tampilkan halaman profil
    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    // Proses update password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'min:6', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        // Gunakan Hash::make atau biarkan model yang melakukan cast
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
