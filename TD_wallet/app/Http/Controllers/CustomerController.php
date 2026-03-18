<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Membership;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\ResetPasswordMail;

class CustomerController extends Controller
{
    // Tampilkan Halaman Login Customer
    public function showLogin()
    {
        return view('customer.login');
    }
    // Proses Login Customer
    public function login(Request $request)
    {
        // 1. Ubah input email menjadi huruf kecil agar tidak case-sensitive
        if ($request->has('email')) {
            $request->merge([
                'email' => strtolower($request->email)
            ]);
        }

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Cari email di database
        $customer = Customer::where('email', $request->email)->first();

        // Jika email tidak terdaftar
        if (!$customer) {
            return back()->withErrors([
                'email' => 'Data email tidak terdaftar di sistem kami.'
            ])->withInput($request->only('email'));
        }

        // 3. BLOKIR JIKA CUSTOMER NON-AKTIF (f_aktif = false/0)
        if (!$customer->f_aktif) {
            return back()->withErrors([
                'email' => 'Akun Anda saat ini dinonaktifkan. Silakan hubungi Admin atau layanan pelanggan.'
            ])->withInput($request->only('email'));
        }

        // 4. Jika email ada dan akun aktif, jalankan Auth
        $credentials = $request->only('email', 'password');
        if (Auth::guard('customer')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('customer.dashboard'));
        }

        // 5. Jika password salah
        return back()->withErrors([
            'password' => 'Credential tidak valid. Pastikan email dan password benar.'
        ])->withInput($request->only('email'));
    }

    // Proses Logout Customer
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
    // Tampilkan halaman paksa ganti password
    public function showForceChangePassword()
    {
        return view('customer.changepassword');
    }

    // Proses update password
    public function updateForcePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed', // Harus ada input 'password_confirmation'
        ], [
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.'
        ]);

        $customer = Auth::guard('customer')->user();

        // Update password (otomatis di-hash oleh model cast)
        $customer->update([
            'password' => $request->password
        ]);

        return redirect()->route('customer.dashboard')->with('success', 'Password berhasil diperbarui! Selamat datang di portal.');
    }

    public function showForgotPasswordForm()
    {
        return view('customer.forgot-password'); // Sesuaikan dengan nama file/folder view Anda
    }

    // 2. Memproses request email reset password
    // 1. UPDATE fungsi ini untuk mengirim email sungguhan
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:customers,email']);
        $email = strtolower($request->email);
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );
        // Buat URL yang mengarah ke form reset password beserta tokennya
        $resetUrl = route('customer.password.reset', ['token' => $token, 'email' => $email]);

        // Kirim email
        Mail::to($email)->send(new ResetPasswordMail($resetUrl));

        return back()->with('success', 'Instruksi reset password telah dikirim ke email Anda! Silakan cek kotak masuk atau folder spam.');
    }

    // 2. TAMBAHKAN fungsi untuk menampilkan form password baru
    public function showResetPasswordForm(Request $request, $token)
    {
        // Ambil email dari URL (?email=...)
        return view('customer.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    // 3. TAMBAHKAN fungsi untuk menyimpan password baru ke database
    public function updatePassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:customers,email',
            'password' => 'required|min:6|confirmed',
            'token'    => 'required'
        ]);

        $email = strtolower($request->email);

        // Cek apakah token valid dan ada di database
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'Token reset password tidak valid atau sudah kedaluwarsa.']);
        }

        // Update password customer
        $customer = Customer::where('email', $email)->first();
        $customer->update([
            'password' => Hash::make($request->password)
        ]);

        // Hapus token yang sudah terpakai agar tidak bisa digunakan lagi
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect()->route('customer.login')->with('success', 'Password berhasil diubah! Silakan login dengan password baru Anda.');
    }
}
