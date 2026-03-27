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
        if (!Hash::check($request->password, $customer->password)) {
            return back()->withErrors(['password' => 'Password salah. Akses ditolak.']);
        }
        session(['qr_authorized' => true]);
        session(['qr_expires_at' => now()->addMinute()]);
        // Simpan pilihan dompet ke session
        session(['qr_wallet_id' => $request->membership_id]);
        return redirect()->route('customer.payment.qr');
    }

    // 3. Tampilkan QR Code
    public function showQr()
    {
        // 1. Pengecekan otorisasi keamanan
        if (!session('qr_authorized') || now()->greaterThan(session('qr_expires_at'))) {
            // Bersihkan semua session jika expired
            session()->forget(['qr_authorized', 'qr_expires_at', 'qr_wallet_id', 'active_qr_token', 'active_qr_expires']);
            return redirect()->route('customer.payment.auth')->with('error', 'Sesi otorisasi telah habis, silakan masukkan sandi kembali.');
        }

        $customer = Auth::guard('customer')->user();

        // Ambil ID dompet dari session
        $membershipId = session('qr_wallet_id');

        // 2. Cek apakah ada token QR transaksi yang masih aktif (Tahan Refresh)
        if (session()->has('active_qr_token') && session()->has('active_qr_expires') && now()->timestamp < session('active_qr_expires')) {
            $uniqueToken = session('active_qr_token');
            $sisaDetik = session('active_qr_expires') - now()->timestamp;
        } else {
            // Jika belum ada atau token lama sudah mati, buat token baru
            $uniqueToken = Str::random(40);
            $sisaDetik = 60; // Waktu hidup QR: 60 detik

            session()->put('active_qr_token', $uniqueToken);
            session()->put('active_qr_expires', now()->addSeconds(60)->timestamp);

            // Simpan ke Cache untuk dibaca oleh mesin Kasir
            Cache::put('qr_status_' . $uniqueToken, 'pending', now()->addSeconds(60));
            Cache::put('qr_customer_' . $uniqueToken, $customer->id, now()->addSeconds(60));

            // KODE BARU: Simpan ID dompet ke Cache agar Kasir tahu harus potong dari mana
            Cache::put('qr_wallet_' . $uniqueToken, $membershipId, now()->addSeconds(60));
        }

        // 3. Enkripsi dan render gambar QR Code
        $encryptedPayload = Crypt::encryptString($uniqueToken);
        $qrCode = QrCode::size(220)->generate($encryptedPayload);

        // KODE BARU: Ambil detail tier untuk ditampilkan di halaman QR
        $membership = $membershipId ? \App\Models\Membership::find($membershipId) : null;

        return view('customer.payment_qr', compact('qrCode', 'customer', 'uniqueToken', 'sisaDetik', 'membership'));
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
