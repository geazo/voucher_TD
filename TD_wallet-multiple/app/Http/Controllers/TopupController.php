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

        $customer = Customer::findOrFail($request->customer_id);
        $requestedPackage = Membership::findOrFail($request->membership_id);

        try {
            DB::transaction(function () use ($customer, $requestedPackage) {
                $operatorId = auth()->id();
                $nominalTopup = $requestedPackage->harga;
                $bonusPoin = $nominalTopup * ($requestedPackage->bonus_topup / 100);

                // Masa berlaku 1 tahun dari sekarang
                $masaBerlaku = now()->addYear();

                // 2. Cari atau Buat Dompet Uang untuk Tier yang dipilih
                $walletUang = Wallet::firstOrCreate(
                    [
                        'customer_id'   => $customer->id,
                        'membership_id' => $requestedPackage->id,
                        'type'          => 'Uang'
                    ],
                    [
                        // Generate No Rekening menggunakan prefix dari master membership
                        'no_rekening' => Wallet::generateNoRekening($requestedPackage->prefix ?? 'CS'),
                        'operator_id' => $operatorId
                    ]
                );

                // 3. Cari atau Buat Dompet Poin untuk Tier yang dipilih
                $walletPoin = Wallet::firstOrCreate(
                    [
                        'customer_id'   => $customer->id,
                        'membership_id' => $requestedPackage->id,
                        'type'          => 'Poin'
                    ],
                    [
                        // Kita samakan nomor rekening Poin dengan nomor rekening Uang di tier ini
                        'no_rekening' => $walletUang->no_rekening,
                        'operator_id' => $operatorId
                    ]
                );

                // 4. Masukkan Saldo Uang Utama (KREDIT) ke Dompet Uang tersebut
                Transaction::create([
                    'wallet_id'   => $walletUang->id,
                    'type'        => 'kredit',
                    'nominal'     => $nominalTopup,
                    'sisa_saldo'  => $nominalTopup,
                    'expired_at'  => $masaBerlaku,
                    'operator_id' => $operatorId,
                    'keterangan'  => 'Topup ' . $requestedPackage->name // Catatan transparan
                ]);

                // 5. Masukkan Saldo Poin Bonus (KREDIT) ke Dompet Poin tersebut
                if ($bonusPoin > 0) {
                    Transaction::create([
                        'wallet_id'   => $walletPoin->id,
                        'type'        => 'kredit',
                        'nominal'     => $bonusPoin,
                        'sisa_saldo'  => $bonusPoin,
                        'expired_at'  => $masaBerlaku,
                        'operator_id' => $operatorId,
                        'keterangan'  => 'Bonus Poin ' . $requestedPackage->name
                    ]);
                }
            });

            return redirect()->route('topup.index')->with('success', 'Topup Paket ' . $requestedPackage->name . ' berhasil diproses!');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
