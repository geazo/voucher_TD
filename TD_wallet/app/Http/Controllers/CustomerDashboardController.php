<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        // Ambil data customer dari guard customer
        $customer = Auth::guard('customer')->user();

        // Ambil saldo dompet
        $customer->load(['wallets']);
        $saldoUang = $customer->wallets->where('type', 'Uang')->first()->balance ?? 0;
        $saldoPoin = $customer->wallets->where('type', 'Poin')->first()->balance ?? 0;

        // Ambil riwayat transaksi, KELOMPOKKAN berdasarkan waktu dan tipe, lalu JUMLAHKAN nominalnya
        $transactions = Transaction::join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.customer_id', $customer->id)
            ->selectRaw('
                transactions.created_at,
                transactions.type,
                SUM(transactions.nominal) as total_nominal,
                MIN(transactions.id) as id
            ')
            ->groupBy('transactions.created_at', 'transactions.type')
            ->orderBy('transactions.created_at', 'desc')
            ->paginate(10);

        return view('customer.dashboard', compact('customer', 'saldoUang', 'saldoPoin', 'transactions'));
    }

    // Fungsi baru untuk merekonstruksi struk dari tabel database
    public function showInvoiceDetail($id)
    {
        $customer = Auth::guard('customer')->user();

        // 1. Cari transaksi yang diklik
        $transaction = Transaction::whereHas('wallet', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id);
        })->findOrFail($id);

        // 2. Tarik SEMUA transaksi milik customer ini yang terjadi pada DETIK YANG SAMA
        // Ini berguna untuk menggabungkan potongan Uang & Poin dalam 1 struk
        $relatedTransactions = Transaction::whereHas('wallet', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id);
        })
            ->where('created_at', $transaction->created_at)
            ->where('type', $transaction->type) // Sama-sama debit atau sama-sama kredit
            ->get();

        // 3. Rekonstruksi data nominal
        $tagihanUang = 0;
        $tagihanPoin = 0;

        foreach ($relatedTransactions as $rt) {
            if ($rt->wallet->type == 'Uang') {
                $tagihanUang += $rt->nominal;
            } else if ($rt->wallet->type == 'Poin') {
                $tagihanPoin += $rt->nominal;
            }
        }

        $total = $tagihanUang + $tagihanPoin;

        // 4. Format data agar persis seperti data dari Cache sebelumnya
        $invoiceData = [
            'total'        => $total,
            'tagihan_uang' => $tagihanUang,
            'tagihan_poin' => $tagihanPoin,
            'waktu'        => $transaction->created_at->format('d M Y, H:i:s'),
            'jenis'        => $transaction->type == 'kredit' ? 'Topup Saldo Berhasil' : 'Pembayaran Berhasil',
            'is_history'   => true // Penanda bahwa ini dibuka dari riwayat
        ];

        // Lempar ke view invoice yang sudah ada
        return view('customer.invoice', compact('invoiceData'));
    }

    public function profile()
    {
        $customer = Auth::guard('customer')->user();
        return view('customer.profile', compact('customer'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'min:6', 'confirmed'],
        ], [
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.'
        ]);

        $customer = Auth::guard('customer')->user();

        // Cek apakah password lama yang diinput cocok dengan di database
        if (!Hash::check($request->current_password, $customer->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        // Update ke password baru
        $customer->update([
            'password' => $request->password
        ]);

        return back()->with('success', 'Password Anda berhasil diperbarui.');
    }


}
