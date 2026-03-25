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

                    // 2. Tambah masa aktif expired_at di tabel transaksi
                    $tx = $extension->transaction;
                    $newExpiredDate = Carbon::parse($tx->expired_at)->addDays($extension->tambahan_hari);

                    $tx->update([
                        'expired_at' => $newExpiredDate
                    ]);
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

    // Menampilkan form pengajuan (Misal dipanggil dari halaman Detail Customer)
    public function createRequest(Request $request)
    {
        // Jaring Pengaman: Jika tidak ada ID yang dikirim
        if (!$request->has('transaction_id') || $request->transaction_id == null) {
            return redirect()->route('dashboard')->with('error', 'Silakan pilih riwayat transaksi customer terlebih dahulu untuk mengajukan perpanjangan.');
        }

        $transaction =  Transaction::with('wallet.customer')->findOrFail($request->transaction_id);

        return view('admin.extension_create', compact('transaction'));
    }

    // Menyimpan pengajuan ke database
    public function storeRequest(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'alasan'         => 'required|string|min:10',
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
