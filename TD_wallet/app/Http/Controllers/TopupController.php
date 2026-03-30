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

        // 2. Ambil data customer & paket yang dipilih
        $customer = Customer::with('wallets.transactions')->findOrFail($request->customer_id);
        $requestedPackage = Membership::findOrFail($request->membership_id);

        // Jika customer sudah punya membership DAN mencoba membeli tier yang berbeda
        if ($customer->membership_id && $customer->membership_id !== $requestedPackage->id) {
            // Hitung sisa saldo uang AKTIF saat ini dari kolom sisa_saldo (yang belum expired)
            $walletUang = $customer->wallets->where('type', 'Uang')->first();
            $saldoUangAktif = $walletUang->transactions()
                ->where('type', 'kredit')
                ->where('sisa_saldo', '>', 0)
                ->where('expired_at', '>=', now()->toDateString())
                ->sum('sisa_saldo');

            if ($saldoUangAktif > 0) {
                return back()->with('error', "Gagal Proses Topup : Customer masih memiliki Saldo Uang Aktif (Rp " . number_format($saldoUangAktif, 0, ',', '.') . ") di tipe membership " . $customer->membership->name . ". Saldo harus habis untuk pindah tipe membership, atau silakan buat akun baru untuk customer ini.");
            }
        }
        // ==========================================
        try {
            DB::transaction(function () use ($customer, $requestedPackage) {
                $operatorId = auth()->id();
                $nominalTopup = $requestedPackage->harga;
                $bonusPoin = $nominalTopup * ($requestedPackage->bonus_topup / 100);

                $walletUang = $customer->wallets->where('type', 'Uang')->first();
                $walletPoin = $customer->wallets->where('type', 'Poin')->first();

                // Masa berlaku 1 tahun dari sekarang
                $masaBerlaku = now()->addYear();

                // 3. Masukkan Saldo Uang Utama (KREDIT)
                Transaction::create([
                    'wallet_id'   => $walletUang->id,
                    'type'        => 'kredit',
                    'nominal'     => $nominalTopup,
                    'sisa_saldo'  => $nominalTopup, // Saldo awal utuh
                    'expired_at'  => $masaBerlaku,  // Kapan hangus
                    'operator_id' => $operatorId
                ]);

                // 4. Masukkan Saldo Poin Bonus (KREDIT)
                if ($bonusPoin > 0) {
                    Transaction::create([
                        'wallet_id'   => $walletPoin->id,
                        'type'        => 'kredit',
                        'nominal'     => $bonusPoin,
                        'sisa_saldo'  => $bonusPoin,
                        'expired_at'  => $masaBerlaku,
                        'operator_id' => $operatorId
                    ]);
                }

                // 5. Update Membership Tier & Sesuaikan Nomor Rekening
                if ($customer->membership_id !== $requestedPackage->id) {
                    // A. Update relasi membership di tabel customer
                    $customer->update([
                        'membership_id' => $requestedPackage->id
                    ]);

                    // B. Tentukan Prefix (2 Huruf) dari paket yang baru dibeli
                    $newPrefix = $requestedPackage->prefix;

                    // C. Update nomor rekening pada SEMUA dompet milik customer ini (Uang & Poin)
                    foreach ($customer->wallets as $dompet) {
                        // Ambil 8 digit terakhir dari nomor rekening lama
                        $kodeUnik = substr($dompet->no_rekening, 2);
                        // Gabungkan dengan prefix baru
                        $dompet->update([
                            'no_rekening' => $newPrefix . $kodeUnik
                        ]);
                    }
                }
            });
            return redirect()->route('topup.index')->with('success', 'Topup Paket ' . $requestedPackage->name . ' berhasil diproses!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
