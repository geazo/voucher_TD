<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BalanceExtension;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminExtensionController extends Controller
{
    // Menampilkan daftar request
    public function index()
    {
        $extensions = BalanceExtension::with(['customer', 'transaction.wallet'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')") // Pending di atas
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.extension_index', compact('extensions'));
    }

    // Memproses Approval / Rejection
    public function process(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject'
        ]);

        $extension = BalanceExtension::with('transaction')->findOrFail($id);

        if ($extension->status !== 'pending') {
            return back()->with('error', 'Request ini sudah diproses sebelumnya.');
        }

        try {
            DB::transaction(function () use ($request, $extension) {
                $operatorId = Auth::id();

                if ($request->action === 'approve') {
                    // 1. Ubah status request
                    $extension->update([
                        'status' => 'approved',
                        'operator_id' => $operatorId
                    ]);

                    // 2. Ambil transaksi perwakilan
                    $tx = $extension->transaction;

                    // 3. CARI SEMUA TRANSAKSI (UANG & POIN) DI WAKTU YANG SAMA
                    $relatedTransactions = \App\Models\Transaction::whereHas('wallet', function ($q) use ($tx) {
                        $q->where('customer_id', $tx->wallet->customer_id);
                    })
                        ->where('created_at', $tx->created_at)
                        ->where('type', 'kredit') // Pastikan hanya Topup
                        ->get();

                    // 4. PERPANJANG SEMUANYA
                    foreach ($relatedTransactions as $rt) {
                        $rt->update([
                            'expired_at' => \Carbon\Carbon::parse($rt->expired_at)->addDays($extension->tambahan_hari)
                        ]);
                    }
                } else {
                    // Jika ditolak, cukup ubah status
                    $extension->update([
                        'status' => 'rejected',
                        'operator_id' => $operatorId
                    ]);
                }
            });

            $pesan = $request->action === 'approve' ? 'Perpanjangan saldo berhasil disetujui!' : 'Pengajuan perpanjangan ditolak.';
            return back()->with('success', $pesan);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses request: ' . $e->getMessage());
        }
    }

    // ==========================================
    // AREA ADMIN (FRONT DESK) - MEMBUAT REQUEST
    // ==========================================

    // Menampilkan form pengajuan dari show customer
    public function createRequest(Request $request)
    {
        // Jaring Pengaman
        if (!$request->has('transaction_id') || $request->transaction_id == null) {
            return redirect()->route('dashboard')->with('error', 'Silakan pilih riwayat transaksi customer terlebih dahulu.');
        }

        $transaction = \App\Models\Transaction::with('wallet.customer')->findOrFail($request->transaction_id);

        // Tarik semua transaksi topup (Uang & Poin) yang terjadi di detik yang sama
        $relatedTransactions = \App\Models\Transaction::with('wallet')
            ->whereHas('wallet', function($q) use ($transaction) {
                $q->where('customer_id', $transaction->wallet->customer_id);
            })
            ->where('created_at', $transaction->created_at)
            ->where('type', 'kredit')
            ->get();

        // Pisahkan variabel sisa saldo
        $saldoUang = 0;
        $saldoPoin = 0;

        foreach ($relatedTransactions as $rt) {
            if ($rt->wallet->type == 'Uang') {
                $saldoUang = $rt->sisa_saldo;
            } elseif ($rt->wallet->type == 'Poin') {
                $saldoPoin = $rt->sisa_saldo;
            }
        }
        
        return view('admin.extension_create', compact('transaction', 'saldoUang', 'saldoPoin'));
    }

    // Menyimpan pengajuan ke database
    public function storeRequest(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'alasan'         => 'required|string|min:5',
            'tambahan_hari'  => 'required|integer|min:1|max:365',
        ]);

        $transaction = Transaction::with('wallet')->findOrFail($request->transaction_id);

        // Cek apakah sudah ada request yang masih pending untuk transaksi ini
        $existingRequest = BalanceExtension::where('transaction_id', $transaction->id)
            ->where('status', 'pending')->first();

        if ($existingRequest) {
            return redirect()->back()->with('error', 'Transaksi ini sedang dalam proses pengajuan perpanjangan.');
        }

        BalanceExtension::create([
            'customer_id'    => $transaction->wallet->customer_id,
            'transaction_id' => $transaction->id,
            'alasan'         => $request->alasan,
            'tambahan_hari'  => $request->tambahan_hari,
            'status'         => 'pending' // Default selalu pending menunggu Super Admin
        ]);

        return redirect()->route('customers.show', $transaction->wallet->customer_id)
            ->with('success', 'Pengajuan perpanjangan saldo berhasil dikirim ke Super Admin.');
    }
}
