<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CustomerPaymentController extends Controller
{
    // 1. Tampilkan Halaman Input Password
    public function showAuth()
    {
        return view('customer.payment_auth');
    }

    // 2. Proses Verifikasi Password
    public function verifyAuth(Request $request)
    {
        $request->validate(['password' => 'required']);
        $customer = Auth::guard('customer')->user();

        // Cek kecocokan password
        if (!Hash::check($request->password, $customer->password)) {
            return back()->withErrors(['password' => 'Password salah. Akses ditolak.']);
        }

        // Jika benar, beri izin akses ke halaman QR selama 5 menit
        session(['qr_authorized' => true]);
        session(['qr_expires_at' => now()->addMinute()]);

        return redirect()->route('customer.payment.qr');
    }

    // 3. Tampilkan QR Code
    public function showQr()
    {
        // 1. Pengecekan otorisasi keamanan (Bawaan aslimu)
        if (!session('qr_authorized') || now()->greaterThan(session('qr_expires_at'))) {
            session()->forget(['qr_authorized', 'qr_expires_at']);
            return redirect()->route('customer.payment.auth')->with('error', 'Sesi otorisasi telah habis, silakan masukkan sandi kembali.');
        }

        $customer = Auth::guard('customer')->user();

        // 2. Cek apakah ada token QR transaksi yang masih aktif (Tahan Refresh)
        if (session()->has('active_qr_token') && session()->has('active_qr_expires') && now()->timestamp < session('active_qr_expires')) {
            // Gunakan token yang sudah ada
            $uniqueToken = session('active_qr_token');
            // Hitung sisa waktu aslinya
            $sisaDetik = session('active_qr_expires') - now()->timestamp;
        } else {
            // Jika belum ada atau token lama sudah mati, buat token baru
            $uniqueToken = Str::random(40);
            $sisaDetik = 60; // Waktu hidup QR: 60 detik

            // Simpan ke Session agar diingat saat halaman di-refresh
            session()->put('active_qr_token', $uniqueToken);
            session()->put('active_qr_expires', now()->addSeconds(60)->timestamp);

            // Simpan ke Cache untuk dibaca oleh mesin Kasir
            Cache::put('qr_status_' . $uniqueToken, 'pending', now()->addSeconds(60));
            Cache::put('qr_customer_' . $uniqueToken, $customer->id, now()->addSeconds(60));
        }

        // 3. Enkripsi dan render gambar QR Code (Ukuran 220 agar rapi di HP)
        $encryptedPayload = Crypt::encryptString($uniqueToken);
        $qrCode = QrCode::size(220)->generate($encryptedPayload);

        // Kirim semua data yang dibutuhkan ke view
        return view('customer.payment_qr', compact('qrCode', 'customer', 'uniqueToken', 'sisaDetik'));
    }
    // Fungsi Baru: Dipanggil diam-diam oleh Javascript HP Customer setiap 2 detik
    public function checkStatus($token)
    {
        // Cek status token di cache
        $status = Cache::get('qr_status_' . $token);
        // Jika kasir sudah scan, kasir akan mengubah status ini menjadi 'success'
        if ($status === 'success') {
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'pending']);
    }

    public function showInvoice($token)
    {
        // Ambil data struk dari Cache yang tadi dibuat oleh Kasir
        $invoiceData = Cache::get('qr_invoice_' . $token);

        if (!$invoiceData) {
            return redirect()->route('customer.dashboard')->with('error', 'Struk tidak ditemukan atau sesi sudah kedaluwarsa.');
        }

        $invoiceData = array_merge([
            'jenis'          => 'Pembayaran Berhasil',
            'invoice_number' => 'TRX-POS-' . time(),
            'kasir_name'     => 'Kasir Taman Dayu',
            'items'          => [],
            'catatan'        => ''
        ], $invoiceData);

        return view('customer.invoice', compact('invoiceData'));
    }
}
