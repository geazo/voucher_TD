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
        $customer = Auth::guard('customer')->user();
        $customer->load(['wallets']);

        $saldoUang = $customer->wallets->where('type', 'Uang')->first()->balance ?? 0;
        $saldoPoin = $customer->wallets->where('type', 'Poin')->first()->balance ?? 0;

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

    // Fungsi baru untuk merekonstruksi struk dari tabel database
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
