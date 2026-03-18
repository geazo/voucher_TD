<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Membership;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TopupController extends Controller
{
    // Method index untuk menampilkan form topup (seperti yang sudah dibahas sebelumnya)
    public function index()
    {
        // Ambil semua paket yang tersedia untuk ditampilkan di pilihan Kasir
        $packages = Membership::orderBy('harga', 'asc')->get();

        // Ambil data customer (bisa menggunakan select2/pencarian nomor telepon di view nantinya)
        $customers = Customer::all();

        return view('topup', compact('packages', 'customers'));
    }

    // Method store untuk memproses topup
    public function store(Request $request)
    {
        // 1. Validasi input dasar
        $request->validate([
            'customer_id'   => 'required|exists:customers,id',
            'membership_id' => 'required|exists:memberships,id',
        ]);
        // 2. Ambil data customer (beserta dompet dan transaksinya) & paket yang dipilih
        $customer = Customer::with('wallets.transactions')->findOrFail($request->customer_id);
        $requestedPackage = Membership::findOrFail($request->membership_id);

        // Jika customer sudah punya membership DAN mencoba membeli tier yang berbeda
        if ($customer->membership_id && $customer->membership_id !== $requestedPackage->id) {
            // Hitung sisa saldo uang saat ini
            $walletUang = $customer->wallets->where('type', 'Uang')->first();
            $kreditUang = $walletUang->transactions->where('type', 'kredit')->sum('nominal');
            $debitUang  = $walletUang->transactions->where('type', 'debit')->sum('nominal');
            $saldoUang  = $kreditUang - $debitUang;
            if ($saldoUang > 0) {
                return back()->with('error', "Gagal Proses Topup : Customer masih memiliki Saldo Uang (Rp " . number_format($saldoUang, 0, ',', '.') . ") di tier " . $customer->membership->name . ". Saldo harus habis habis untuk pindah tier, atau silakan buat akun baru untuk customer ini.");
            }
        }
        // ==========================================
        try {
            DB::transaction(function () use ($customer, $requestedPackage) {
                $operatorId = auth()->id();
                $nominalTopup = $requestedPackage->harga;
                $bonusPoin = $nominalTopup * ($requestedPackage->bonus_topup / 100);
                // cek wallet Uang & Poin customer
                $walletUang = $customer->wallets->where('type', 'Uang')->first();
                $walletPoin = $customer->wallets->where('type', 'Poin')->first();
                // 3. Masukkan Saldo Uang Utama
                Transaction::create([
                    'wallet_id'   => $walletUang->id,
                    'nominal'     => $nominalTopup,
                    'type'        => 'kredit',
                    'operator_id' => $operatorId
                ]);
                // 4. Masukkan Saldo Poin Bonus (Jika ada)
                if ($bonusPoin > 0) {
                    Transaction::create([
                        'wallet_id'   => $walletPoin->id,
                        'nominal'     => $bonusPoin,
                        'type'        => 'kredit',
                        'operator_id' => $operatorId
                    ]);
                }
                // 5. Update Membership Tier (hanya jika belum punya, atau jika tiernya berubah setelah saldo habis)
                if ($customer->membership_id !== $requestedPackage->id) {
                    $customer->update([
                        'membership_id' => $requestedPackage->id
                    ]);
                }
            });
            return redirect()->route('topup.index')->with('success', 'Topup Paket ' . $requestedPackage->name . ' berhasil diproses!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
