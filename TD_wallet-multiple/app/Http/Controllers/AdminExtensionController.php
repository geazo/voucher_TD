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
        $request->validate(['action' => 'required|in:approve,reject']);

        // Pastikan relasi transaction.wallet ter-load
        $extension = BalanceExtension::with('transaction.wallet')->findOrFail($id);

        if ($extension->status !== 'pending') {
            return back()->with('error', 'Request ini sudah diproses sebelumnya.');
        }

        try {
            DB::transaction(function () use ($request, $extension) {
                $operatorId = Auth::id();

                if ($request->action === 'approve') {
                    $extension->update([
                        'status'      => 'approved',
                        'operator_id' => $operatorId
                    ]);

                    $tx = $extension->transaction;

                    // 1. CARI TRANSAKSI TERKAIT
                    $walletTier = $tx->wallet->membership_id;
                    $relatedTransactions = Transaction::whereHas('wallet', function ($q) use ($tx, $walletTier) {
                        $q->where('customer_id', $tx->wallet->customer_id)
                            ->where('membership_id', $walletTier);
                    })
                        ->where('created_at', $tx->created_at)
                        ->where('type', 'kredit')
                        ->get();

                    // 2. LOGIKA EXTENSION
                    if ($extension->is_recovery) {

                        $masaBerlakuBaru = now()->addDays($extension->tambahan_hari);

                        foreach ($relatedTransactions as $rt) {
                            $nominalSuntikan = ($rt->wallet->type == 'Uang') ? $extension->nominal_uang : $extension->nominal_poin;

                            if ($nominalSuntikan > 0) {
                                Transaction::create([
                                    'wallet_id'   => $rt->wallet_id,
                                    'type'        => 'kredit',
                                    'nominal'     => $nominalSuntikan,
                                    'sisa_saldo'  => $nominalSuntikan,
                                    'expired_at'  => $masaBerlakuBaru,
                                    'operator_id' => $operatorId,
                                    'keterangan'  => 'Pemulihan Saldo (Ref: TX-' . $rt->id . ')'
                                ]);
                            }
                        }
                    } else {
                        // JIKA PERPANJANGAN (Update Tanggal Saja)
                        foreach ($relatedTransactions as $rt) {
                            $rt->update([
                                'expired_at' => \Carbon\Carbon::parse($rt->expired_at)->addDays($extension->tambahan_hari)
                            ]);
                        }
                    }
                } else {
                    // Jika ditolak
                    $extension->update([
                        'status'      => 'rejected',
                        'operator_id' => $operatorId
                    ]);
                }
            });

            $pesan = $request->action === 'approve' ? 'Saldo berhasil diproses!' : 'Pengajuan ditolak.';
            return back()->with('success', $pesan);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses request: ' . $e->getMessage());
        }
    }

    // ==========================================
    // AREA ADMIN - MEMBUAT REQUEST
    // ==========================================

    // Menampilkan form pengajuan dari show customer
    public function createRequest(Request $request)
    {
        if (!$request->has('transaction_id') || $request->transaction_id == null) {
            return redirect()->route('dashboard')->with('error', 'Silakan pilih riwayat transaksi customer terlebih dahulu.');
        }

        $transaction = Transaction::with('wallet.customer')->findOrFail($request->transaction_id);
        $walletTier = $transaction->wallet->membership_id;
        // CARI TRANSAKSI UANG & POIN HANYA DI TIER DOMPET YANG SAMA
        $relatedTransactions = Transaction::with('wallet')
            ->whereHas('wallet', function ($q) use ($transaction, $walletTier) {
                $q->where('customer_id', $transaction->wallet->customer_id)
                    ->where('membership_id', $walletTier); // Isolasi Multi-Wallet
            })
            ->where('created_at', $transaction->created_at)
            ->where('type', 'kredit')
            ->get();

        $saldoUang = 0;
        $saldoPoin = 0;
        $isRecovery = false;

        foreach ($relatedTransactions as $rt) {
            $nominal = $rt->sisa_saldo;

            // LOGIKA RECOVERY: JIKA SALDO 0, CARI NOMINAL YANG HANGUS DARI ADJUSTMENT
            if ($nominal == 0) {
                $isRecovery = true;
                $adjustment = Transaction::where('wallet_id', $rt->wallet_id)
                    ->where('type', 'adjustment')
                    ->whereDate('created_at', '>=', \Carbon\Carbon::parse($rt->expired_at)->toDateString())
                    ->orderBy('id', 'desc')
                    ->first();

                $nominal = $adjustment ? $adjustment->nominal : 0;
            }

            if ($rt->wallet->type == 'Uang') $saldoUang = $nominal;
            if ($rt->wallet->type == 'Poin') $saldoPoin = $nominal;
        }

        return view('admin.extension_create', compact('transaction', 'saldoUang', 'saldoPoin', 'isRecovery'));
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
        $pendingRequest = BalanceExtension::where('transaction_id', $transaction->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest) {
            return redirect()->back()->with('error', 'Transaksi ini sedang dalam proses menunggu persetujuan Super Admin.');
        }
        // check apa pemulihan
        if ($request->is_recovery) {
            $recoveredRequest = BalanceExtension::where('transaction_id', $transaction->id)
                ->where('status', 'approved')
                ->where('is_recovery', true)
                ->first();

            if ($recoveredRequest) {
                return redirect()->back()->with('error', 'Saldo hangus pada transaksi ini sudah pernah dipulihkan menjadi saldo baru.');
            }
        }
        BalanceExtension::create([
            'customer_id'    => $transaction->wallet->customer_id,
            'transaction_id' => $transaction->id,
            'alasan'         => $request->alasan,
            'tambahan_hari'  => $request->tambahan_hari,
            'is_recovery'    => $request->is_recovery ?? false,
            'nominal_uang'   => $request->nominal_uang ?? 0,
            'nominal_poin'   => $request->nominal_poin ?? 0,
            'status'         => 'pending'
        ]);
        $customerId = $transaction->wallet->customer_id;
        $memId = $transaction->wallet->membership_id ?? 'default';
        return redirect()->route('customers.show', ['customer' => $customerId, 'membership_id' => $memId === 'default' ? '' : $memId])
            ->with('success', 'Pengajuan perpanjangan saldo berhasil dikirim, menunggu persetujuan.');
    }
}
