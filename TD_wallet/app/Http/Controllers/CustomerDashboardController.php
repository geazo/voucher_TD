<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class CustomerDashboardController extends Controller
{
    // =================================
    // FUNGSI UNTUK DASHBOARD & TOPUP INFO
    // =================================
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $customer->load(['wallets']);

        $dompetUang = $customer->wallets->where('type', 'Uang')->first();
        $dompetPoin = $customer->wallets->where('type', 'Poin')->first();

        $saldoUang = $dompetUang ? $dompetUang->balance : 0;
        $saldoPoin = $dompetPoin ? $dompetPoin->balance : 0;

        // Tambahkan MAX(order_id) dan MAX(keterangan) agar tidak error saat GROUP BY
        $transactions = Transaction::join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.customer_id', $customer->id)
            ->selectRaw('
                transactions.created_at,
                transactions.type,
                SUM(transactions.nominal) as total_nominal,
                MIN(transactions.id) as id,
                MAX(transactions.order_id) as order_id,
                MAX(transactions.keterangan) as keterangan
            ')
            ->groupBy('transactions.created_at', 'transactions.type')
            ->orderBy('transactions.created_at', 'desc')
            ->paginate(10);

        $activeBalances = \App\Models\Transaction::join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->where('wallets.customer_id', $customer->id)
            ->where('transactions.type', 'kredit')
            ->where('transactions.sisa_saldo', '>', 0)
            ->select('transactions.*', 'wallets.type as wallet_type')
            ->orderBy('transactions.expired_at', 'asc') // Urutkan dari yang paling cepat kedaluwarsa
            ->get();

        return view('customer.dashboard', compact('customer', 'saldoUang', 'saldoPoin', 'transactions', 'activeBalances'));
    }
    public function saldoInfo()
    {
        // 1. Ambil data customer yang sedang login
        $customer = Auth::guard('customer')->user()->load(['membership', 'wallets']);

        $dompetUang = $customer->wallets->where('type', 'Uang')->first();
        $dompetPoin = $customer->wallets->where('type', 'Poin')->first();

        // 2. Ambil SEMUA transaksi topup (kredit) yang saldonya MASIH ADA (> 0)
        // Urutkan dari yang paling cepat kedaluwarsa (asc)
        $activeTopups = \App\Models\Transaction::with('wallet')
            ->whereIn('wallet_id', $customer->wallets->pluck('id'))
            ->where('type', 'kredit')
            ->where('sisa_saldo', '>', 0)
            ->orderBy('expired_at', 'asc')
            ->get();

        // 3. Kelompokkan berdasarkan waktu Topup agar Uang & Poin menyatu di View
        $groupedTopups = $activeTopups->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d H:i:s');
        });

        return view('customer.saldo', compact('customer', 'dompetUang', 'dompetPoin', 'groupedTopups'));
    }

    // =================================
    // FUNGSI UNTUK FITUR INVOICE
    // =================================
    public function showInvoiceDetail($id)
    {
        $customer = Auth::guard('customer')->user();

        // 1. Cari transaksi yang diklik beserta relasi Nota-nya (jika ada)
        $transaction = Transaction::with(['wallet', 'order.details', 'operator'])->whereHas('wallet', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id);
        })->findOrFail($id);

        // 2. CEK: Apakah ini dari transaksi POS Kasir (Punya Order ID)?
        if ($transaction->order_id) {
            $order = $transaction->order;

            $invoiceData = [
                'invoice_number' => $order->invoice_number,
                'total'          => $order->total_tagihan,
                'tagihan_uang'   => $order->bayar_uang,
                'tagihan_poin'   => $order->bayar_poin,
                'waktu'          => $order->created_at->format('d M Y, H:i'),
                'jenis'          => 'Pembayaran POS Berhasil',
                'items'          => $order->details, // Mengirim rincian barang dari database
                'is_history'     => true,
                'kasir_name'     => $transaction->operator->nama ?? 'Kasir',
                'catatan'        => $transaction->keterangan
            ];
        }
        // 3. JIKA BUKAN POS (Ini adalah Topup, Saldo Hangus, dsb.)
        else {
            // Rekonstruksi data nominal dari waktu yang sama
            $relatedTransactions = Transaction::whereHas('wallet', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
                ->where('created_at', $transaction->created_at)
                ->where('type', $transaction->type)
                ->get();

            $tagihanUang = 0;
            $tagihanPoin = 0;

            foreach ($relatedTransactions as $rt) {
                if ($rt->wallet->type == 'Uang') {
                    $tagihanUang += $rt->nominal;
                } else if ($rt->wallet->type == 'Poin') {
                    $tagihanPoin += $rt->nominal;
                }
            }

            // Tentukan jenis transaksi berdasarkan Tipe dan Keterangan
            $jenisTx = 'Penyesuaian Saldo';
            if ($transaction->type == 'kredit') {
                $jenisTx = 'Topup Saldo Berhasil';
            } elseif (str_contains(strtolower($transaction->keterangan), 'kedaluwarsa')) {
                $jenisTx = 'Saldo Kedaluwarsa (Hangus)';
            }

            $invoiceData = [
                'invoice_number' => 'TRX-' . $transaction->created_at->format('YmdHis'),
                'total'          => $tagihanUang + $tagihanPoin,
                'tagihan_uang'   => $tagihanUang,
                'tagihan_poin'   => $tagihanPoin,
                'waktu'          => $transaction->created_at->format('d M Y, H:i'),
                'jenis'          => $jenisTx,
                'items'          => [], // KOSONG KARENA BUKAN BELANJA ITEM
                'is_history'     => true,
                'kasir_name'     => $transaction->operator->nama ?? 'Sistem',
                'catatan'        => $transaction->keterangan ?? '-'
            ];
        }

        return view('customer.invoice', compact('invoiceData'));
    }
    // =================================
    // FUNGSI UNTUK FITUR PROFIL & GANTI PASSWORD
    // =================================
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
