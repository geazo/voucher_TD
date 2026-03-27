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
        $extensions = BalanceExtension::with(['customer.wallets', 'transaction.wallet'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.extension_index', compact('extensions'));
    }

    // Memproses Approval / Rejection
    public function process(Request $request, $id)
    {
        $request->validate(['action' => 'required|in:approve,reject']);

        $extension = BalanceExtension::with('transaction')->findOrFail($id);

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

                    // Cari transaksi yang berkaitan (Uang & Poin)
                    $relatedTransactions = \App\Models\Transaction::whereHas('wallet', function ($q) use ($tx) {
                        $q->where('customer_id', $tx->wallet->customer_id);
                    })
                        ->where('created_at', $tx->created_at)
                        ->where('type', 'kredit')
                        ->get();

                    // LOGIKA BERCABANG: PEMULIHAN vs PERPANJANGAN
                    if ($extension->is_recovery) {
                        // 1. JIKA PEMULIHAN (Suntik Saldo Baru)
                        $masaBerlakuBaru = now()->addDays($extension->tambahan_hari);

                        foreach ($relatedTransactions as $rt) {
                            $nominalSuntikan = ($rt->wallet->type == 'Uang') ? $extension->nominal_uang : $extension->nominal_poin;

                            // Hanya buat transaksi jika ada nominal yang dipulihkan
                            if ($nominalSuntikan > 0) {
                                \App\Models\Transaction::create([
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
                        // 2. JIKA PERPANJANGAN BIASA (Update Tanggal Saja)
                        foreach ($relatedTransactions as $rt) {
                            $rt->update([
                                'expired_at' => \Carbon\Carbon::parse($rt->expired_at)->addDays($extension->tambahan_hari)
                            ]);
                        }
                    }
                } else {
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

        $relatedTransactions = Transaction::with('wallet')
            ->whereHas('wallet', function ($q) use ($transaction) {
                $q->where('customer_id', $transaction->wallet->customer_id);
            })
            ->where('created_at', $transaction->created_at)
            ->where('type', 'kredit')
            ->get();

        $saldoUang = 0;
        $saldoPoin = 0;
        $isRecovery = false;

        foreach ($relatedTransactions as $rt) {
            $nominal = $rt->sisa_saldo;

            // JIKA SALDO 0, KEMUNGKINAN SUDAH EXPIRED. KITA CARI NOMINALNYA DARI TRANSAKSI ADJUSTMENT
            if ($nominal == 0) {
                $isRecovery = true;
                // Cari transaksi pemotongan (adjustment) sistem setelah tanggal expired
                $adjustment = Transaction::where('wallet_id', $rt->wallet_id)
                    ->where('type', 'adjustment')
                    ->whereDate('created_at', '>=', \Carbon\Carbon::parse($rt->expired_at)->toDateString())
                    ->orderBy('id', 'desc')
                    ->first();

                // Jika ketemu, itulah nominal yang hangus. Jika tidak, berarti memang habis dipakai belanja (0)
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
            'alasan'         => 'required|string|min:10',
            'tambahan_hari'  => 'required|integer|min:1|max:365',
            'is_recovery'    => 'required|boolean',
            'nominal_uang'   => 'required|numeric|min:0',
            'nominal_poin'   => 'required|numeric|min:0',
        ]);

        $transaction = Transaction::with('wallet')->findOrFail($request->transaction_id);

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
            'is_recovery'    => $request->is_recovery,
            'nominal_uang'   => $request->nominal_uang,
            'nominal_poin'   => $request->nominal_poin,
            'status'         => 'pending'
        ]);

        return redirect()->route('customers.show', $transaction->wallet->customer_id)
            ->with('success', 'Pengajuan berhasil dikirim ke Super Admin.');
    }
}
