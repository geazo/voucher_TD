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
        $customer->load(['wallets.membership']);

        $walletsGrouped = $customer->wallets->groupBy('membership_id');

        // 1. Petakan semua dompet yang dimiliki customer
        $allCards = $walletsGrouped->map(function ($wallets, $membershipId) {
            $dompetUang = $wallets->where('type', 'Uang')->first();
            $dompetPoin = $wallets->where('type', 'Poin')->first();
            $membership = $wallets->first()->membership;

            $saldoUang = $dompetUang ? $dompetUang->balance : 0;
            $saldoPoin = $dompetPoin ? $dompetPoin->balance : 0;

            return (object) [
                'membership_id' => $membership ? $membership->id : 'default', // Gunakan 'default' jika null
                'membership'    => $membership,
                'no_rekening'   => $dompetUang ? $dompetUang->no_rekening : ($dompetPoin ? $dompetPoin->no_rekening : 'N/A'),
                'saldoUang'     => $saldoUang,
                'saldoPoin'     => $saldoPoin,
                'totalSaldo'    => $saldoUang + $saldoPoin,
                'wallet_ids'    => $wallets->pluck('id')->toArray()
            ];
        })->values();

        // 2. LOGIKA SMART VISIBILITY
        // Pisahkan dompet default (Customer biasa) dan dompet Premium (Punya Tier)
        $defaultCard = $allCards->where('membership_id', 'default')->first();

        // Ambil dompet Premium yang SALDO-nya LEBIH DARI 0
        $activePremiumCards = $allCards->filter(function ($card) {
            return $card->membership_id !== 'default' && $card->totalSaldo > 0;
        })->values();

        // dompet default
        if ($activePremiumCards->count() > 0) {
            $displayCards = $activePremiumCards;
        } else {
            $displayCards = $defaultCard ? collect([$defaultCard]) : collect();
        }

        // 3. Ambil Transaksi HANYA untuk dompet yang sedang ditampilkan
        $allowedWalletIds = [];
        foreach ($displayCards as $c) {
            $allowedWalletIds = array_merge($allowedWalletIds, $c->wallet_ids);
        }

        $transactions = Transaction::join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->whereIn('wallets.id', $allowedWalletIds)
            ->selectRaw('
                transactions.created_at,
                transactions.type,
                SUM(transactions.nominal) as total_nominal,
                MIN(transactions.id) as id,
                MAX(transactions.order_id) as order_id,
                MAX(transactions.keterangan) as keterangan,
                MAX(wallets.membership_id) as membership_id
            ')
            ->groupBy('transactions.created_at', 'transactions.type')
            ->orderBy('transactions.created_at', 'desc')
            ->paginate(10);

        return view('customer.dashboard', compact('customer', 'displayCards', 'transactions'));
    }

    public function saldoInfo(Request $request)
    {
        // 1. Ambil data customer yang sedang login
        $customer = Auth::guard('customer')->user();
        $customer->load(['wallets.membership']);

        // 2. Tangkap membership_id dari URL (?membership_id=...)
        $membershipId = $request->query('membership_id');

        // 3. Filter dompet HANYA untuk membership yang dipilih
        if ($membershipId) {
            $wallets = $customer->wallets->where('membership_id', $membershipId);
        } else {
            // Jika membership_id kosong, berarti user mengeklik kartu 'Customer' default
            $wallets = $customer->wallets->whereNull('membership_id');
        }

        // [Security Check] Jika URL dimanipulasi dan dompet tidak ditemukan
        if ($wallets->isEmpty()) {
            return redirect()->route('customer.dashboard')->with('error', 'Rekening tidak ditemukan atau belum aktif.');
        }

        // 4. Pisahkan dompet Uang dan Poin KHUSUS untuk kartu/tier ini
        $dompetUang = $wallets->where('type', 'Uang')->first();
        $dompetPoin = $wallets->where('type', 'Poin')->first();

        // Ambil objek membership untuk pewarnaan dinamis di View
        $membership = $wallets->first()->membership;

        // 5. Ambil data Topup aktif KHUSUS untuk dompet di tier ini
        $activeTopups = \App\Models\Transaction::with('wallet')
            ->whereIn('wallet_id', $wallets->pluck('id')) // Filter berdasarkan wallet_id yang valid
            ->where('type', 'kredit')
            ->where('sisa_saldo', '>', 0)
            ->orderBy('expired_at', 'asc')
            ->get();

        // 6. Kelompokkan berdasarkan waktu Topup
        $groupedTopups = $activeTopups->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d H:i:s');
        });

        // Lemparkan $membership ke view agar bisa digambar dengan warna yang sesuai
        return view('customer.saldo', compact('customer', 'dompetUang', 'dompetPoin', 'groupedTopups', 'membership'));
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
