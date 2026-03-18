<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OperatorPaymentController extends Controller
{
    // 1. Tampilkan Halaman Kasir (Scanner & POS)
    public function showScanner()
    {
        return view('admin.pos');
    }

    // 2. Proses Hasil Scan & Potong Saldo
    public function processPayment(Request $request)
    {
        // Validasi menggunakan nominal_total
        $request->validate([
            'qr_payload'    => 'required',
            'nominal_total' => 'required|numeric|min:1000',
            'cart_data'     => 'nullable'
        ]);

        try {
            // 1. Validasi Token QR (Sistem mencoba membaca enkripsi rahasia)
            $uniqueToken = Crypt::decryptString($request->qr_payload);
            $customerId = Cache::get('qr_customer_' . $uniqueToken);
            $tokenStatus = Cache::get('qr_status_' . $uniqueToken);

            if (!$customerId || $tokenStatus !== 'pending') {
                return back()->with('error', 'QR Code tidak valid atau sudah kedaluwarsa (lebih dari 1 menit).');
            }

            $customer = Customer::with(['wallets', 'membership'])->findOrFail($customerId);
            // 2. Cek Dompet & Saldo
            // PENTING: Tambahkan tanda ?-> agar kebal error meskipun dompet belum ada di database
            $dompetUang = $customer->wallets->where('type', 'Uang')->first();
            $dompetPoin = $customer->wallets->where('type', 'Poin')->first();
            $saldoUang  = $dompetUang?->balance ?? 0;
            $saldoPoin  = $dompetPoin?->balance ?? 0;

            // 3. Kalkulasi Partial Burn (Logika Diskon Poin)
            $totalTagihan = $request->nominal_total;
            // PENTING: Tambahkan tanda ?-> agar kebal error jika customer ini tidak memiliki membership
            $persenDiskon = $customer->membership?->diskon_belanja ?? 0;

            // Hitung IDEAL potongan poinnya berdasarkan persentase diskon membership
            $maksimalDiskonPoin = $totalTagihan * ($persenDiskon / 100);

            $tagihanPoin = 0;
            $tagihanUang = $totalTagihan;
            $pesanNotifikasi = '';

            if ($maksimalDiskonPoin > 0) {
                if ($saldoPoin <= 0) {
                    $tagihanPoin = 0;
                    $tagihanUang = $totalTagihan;
                    $pesanNotifikasi = "Saldo poin kosong, tagihan dibebankan penuh ke saldo uang.";
                } elseif ($saldoPoin < $maksimalDiskonPoin) {
                    $tagihanPoin = $saldoPoin;
                    $tagihanUang = $totalTagihan - $tagihanPoin;
                    $pesanNotifikasi = "Poin tidak cukup untuk full diskon {$persenDiskon}%, poin dihabiskan dan sisanya dibebankan ke saldo uang.";
                } else {
                    $tagihanPoin = $maksimalDiskonPoin;
                    $tagihanUang = $totalTagihan - $tagihanPoin;
                }
            }

            // 4. Final Cek Kecukupan Saldo Uang
            if ($saldoUang < $tagihanUang) {
                return back()->with('error', "Saldo Uang tidak cukup! Tagihan akhir: Rp " . number_format($tagihanUang, 0, ',', '.') . ", Saldo pelanggan: Rp " . number_format($saldoUang, 0, ',', '.'));
            }

            // 5. Proses Database (Gunakan Transaction agar aman)
            DB::beginTransaction();
            try {
                $operatorId = Auth::id();

                // 5A. Catat pemotongan saldo Uang
                Transaction::create([
                    'wallet_id'   => $dompetUang->id,
                    'type'        => 'debit',
                    'nominal'     => $tagihanUang,
                    'operator_id' => $operatorId
                ]);

                // 5B. Catat pemotongan saldo Poin (Hanya jika poin terpakai)
                if ($tagihanPoin > 0) {
                    Transaction::create([
                        'wallet_id'   => $dompetPoin->id,
                        'type'        => 'debit',
                        'nominal'     => $tagihanPoin,
                        'operator_id' => $operatorId
                    ]);
                }

                // --- UPDATE LAYAR CUSTOMER ---
                Cache::put('qr_status_' . $uniqueToken, 'success', now()->addMinutes(1));
                Cache::put('qr_invoice_' . $uniqueToken, [
                    'total'        => $totalTagihan,
                    'tagihan_uang' => $tagihanUang,
                    'tagihan_poin' => $tagihanPoin,
                    'waktu'        => now()->format('d M Y, H:i')
                ], now()->addMinutes(5));

                DB::commit();

                // --- UPDATE LAYAR KASIR ---
                $invoiceData = [
                    'customer_name' => $customer->nama,
                    'total'         => $totalTagihan,
                    'tagihan_uang'  => $tagihanUang,
                    'tagihan_poin'  => $tagihanPoin,
                    'waktu'         => now()->format('d M Y, H:i:s'),
                    'kasir_name'    => Auth::user()->nama ?? 'Kasir',
                    'catatan'       => $pesanNotifikasi
                ];

                return back()->with('success_invoice', $invoiceData);
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Gagal mencatat transaksi ke database: ' . $e->getMessage());
            }

        } catch (DecryptException $e) {
            // Jika masuk ke sini, berarti kasir men-scan QR Code yang salah/bukan buatan aplikasi
            return back()->with('error', 'Format QR Code tidak dikenali. Pastikan Anda HANYA men-scan QR dari HP Customer aplikasi ini.');
        } catch (\Throwable $e) {

            return back()->with('error', 'Sistem mendeteksi masalah: ' . $e->getMessage() . ' di baris ' . $e->getLine());
        }
    }
}
