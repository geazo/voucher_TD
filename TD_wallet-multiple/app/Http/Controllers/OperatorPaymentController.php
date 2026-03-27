<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Transaction;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OperatorPaymentController extends Controller
{
    // 1. Tampilkan Halaman Kasir (Scanner & POS)
    public function showScanner()
    {
        $items = Item::orderBy('nama', 'asc')->get();
        return view('admin.pos', compact('items'));
    }

    // 2. Proses Hasil Scan & Potong Saldo
    public function processPayment(Request $request)
    {
        $request->validate([
            'qr_payload'    => 'required',
            'nominal_total' => 'required|numeric|min:1000',
            'cart_data'     => 'nullable'
        ]);

        try {
            $uniqueToken = Crypt::decryptString($request->qr_payload);
            $customerId = Cache::get('qr_customer_' . $uniqueToken);
            $tokenStatus = Cache::get('qr_status_' . $uniqueToken);

            // 1. TARIK PILIHAN DOMPET DARI CACHE
            $selectedMembershipId = Cache::get('qr_wallet_' . $uniqueToken);

            // dd([
            //     'ID_Dompet_yang_ditangkap_Kasir' => $selectedMembershipId,
            //     'Token_QR_yang_dipakai' => $uniqueToken
            // ]);

            if (!$customerId || $tokenStatus !== 'pending') {
                return back()->with('error', 'QR Code tidak valid atau sudah kedaluwarsa.');
            }

            // Load relasi wallets beserta membership-nya
            $customer = Customer::with(['wallets.membership'])->findOrFail($customerId);

            // 2. FILTER DOMPET SESUAI PILIHAN CUSTOMER
            if ($selectedMembershipId) {
                $wallets = $customer->wallets->where('membership_id', $selectedMembershipId);
            } else {
                $wallets = $customer->wallets->whereNull('membership_id');
            }

            if ($wallets->isEmpty()) {
                return back()->with('error', 'Gagal memproses. Dompet yang dipilih pelanggan tidak ditemukan/tidak aktif.');
            }

            $dompetUang = $wallets->where('type', 'Uang')->first();
            $dompetPoin = $wallets->where('type', 'Poin')->first();

            // 3. AMBIL DISKON DARI MEMBERSHIP DOMPET TERSEBUT
            $membership = $dompetUang->membership ?? null;
            $persenDiskon = $membership ? $membership->diskon_belanja : 0;

            // 4. CEK SALDO AKTIF (Abaikan yang sisa_saldo 0 dan yang sudah expired)
            $saldoUang = Transaction::where('wallet_id', $dompetUang->id)
                ->where('type', 'kredit')
                ->where('sisa_saldo', '>', 0)
                ->where('expired_at', '>=', now()->toDateString())
                ->sum('sisa_saldo');

            $saldoPoin = Transaction::where('wallet_id', $dompetPoin->id)
                ->where('type', 'kredit')
                ->where('sisa_saldo', '>', 0)
                ->where('expired_at', '>=', now()->toDateString())
                ->sum('sisa_saldo');

            // 5. KALKULASI PARTIAL BURN (Logika Diskon Poin)
            $totalTagihan = $request->nominal_total;
            $maksimalDiskonPoin = $totalTagihan * ($persenDiskon / 100);

            $tagihanPoin = 0;
            $tagihanUang = $totalTagihan;
            $pesanNotifikasi = '';

            if ($maksimalDiskonPoin > 0) {
                if ($saldoPoin <= 0) {
                    $pesanNotifikasi = "Saldo poin kosong/expired, tagihan dibebankan penuh ke saldo uang.";
                } elseif ($saldoPoin < $maksimalDiskonPoin) {
                    $tagihanPoin = $saldoPoin;
                    $tagihanUang = $totalTagihan - $tagihanPoin;
                    $pesanNotifikasi = "Poin tidak cukup untuk full diskon {$persenDiskon}%, poin dihabiskan dan sisanya dibebankan ke saldo uang.";
                } else {
                    $tagihanPoin = $maksimalDiskonPoin;
                    $tagihanUang = $totalTagihan - $tagihanPoin;
                }
            }

            if ($saldoUang < $tagihanUang) {
                return back()->with('error', "Saldo Uang tidak cukup! Tagihan akhir: Rp " . number_format($tagihanUang, 0, ',', '.') . ", Saldo aktif dompet ini: Rp " . number_format($saldoUang, 0, ',', '.'));
            }

            // 6. PROSES DATABASE DENGAN TRANSACTION
            DB::beginTransaction();
            try {
                $operatorId = Auth::id();

                // 6A. Buat Header Nota (Tabel orders)
                $order = Order::create([
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                    'customer_id'    => $customer->id,
                    'operator_id'    => $operatorId,
                    'total_tagihan'  => $totalTagihan,
                    'bayar_uang'     => $tagihanUang,
                    'bayar_poin'     => $tagihanPoin,
                ]);

                // 6B. Decode data keranjang
                $cartData = json_decode($request->cart_data, true) ?? [];

                // =======================================================================
                // OPSI 1: MERGE ITEM (GABUNGKAN ITEM YANG SAMA)
                // =======================================================================
                $mergedCart = [];
                foreach ($cartData as $item) {
                    $itemId = $item['id'];
                    if (isset($mergedCart[$itemId])) {
                        $mergedCart[$itemId]['qty'] += $item['qty'];
                        $mergedCart[$itemId]['subtotal'] += $item['subtotal'];
                    } else {
                        $mergedCart[$itemId] = $item;
                    }
                }

                foreach ($mergedCart as $item) {
                    OrderDetail::create([
                        'order_id'   => $order->id,
                        'item_id'    => $item['id'],
                        'item_name'  => $item['name'],
                        'price'      => $item['price'],
                        'qty'        => $item['qty'],
                        'subtotal'   => $item['subtotal']
                    ]);
                }

                // =======================================================================
                // 6C. FUNGSI HELPER UNTUK POTONG SALDO (SISTEM FEFO)
                // =======================================================================
                $potongSaldoFefo = function ($walletId, $jumlahPotong) {
                    $kreditTersedia = Transaction::where('wallet_id', $walletId)
                        ->where('type', 'kredit')
                        ->where('sisa_saldo', '>', 0)
                        ->where('expired_at', '>=', now()->toDateString())
                        ->orderBy('expired_at', 'asc') // Paling mau expired (FEFO)
                        ->orderBy('created_at', 'asc') // Fallback jika tanggal expired sama
                        ->lockForUpdate() // Cegah race condition
                        ->get();

                    $sisaTagihan = $jumlahPotong;

                    foreach ($kreditTersedia as $kredit) {
                        if ($sisaTagihan <= 0) break;

                        if ($kredit->sisa_saldo >= $sisaTagihan) {
                            $kredit->sisa_saldo -= $sisaTagihan;
                            $kredit->save();
                            $sisaTagihan = 0;
                        } else {
                            $sisaTagihan -= $kredit->sisa_saldo;
                            $kredit->sisa_saldo = 0;
                            $kredit->save();
                        }
                    }

                    // Fail-Safe: Batalkan semua jika ada sisa tagihan yang tidak terpotong
                    if ($sisaTagihan > 0) {
                        throw new \Exception("Gagal memotong saldo secara penuh. Saldo aktif tidak sinkron dengan tagihan.");
                    }
                };

                // 6D. Eksekusi Pemotongan Poin & Uang
                if ($tagihanPoin > 0) {
                    $potongSaldoFefo($dompetPoin->id, $tagihanPoin);
                    Transaction::create([
                        'wallet_id'   => $dompetPoin->id,
                        'order_id'    => $order->id,
                        'type'        => 'debit',
                        'nominal'     => $tagihanPoin,
                        'operator_id' => $operatorId,
                        'keterangan'  => 'Pembayaran - ' . $order->invoice_number
                    ]);
                }

                $potongSaldoFefo($dompetUang->id, $tagihanUang);
                Transaction::create([
                    'wallet_id'   => $dompetUang->id,
                    'order_id'    => $order->id,
                    'type'        => 'debit',
                    'nominal'     => $tagihanUang,
                    'operator_id' => $operatorId,
                    'keterangan'  => 'Pembayaran - ' . $order->invoice_number
                ]);

                $order->load('details');

                // 7. SIMPAN DATA INVOICE KE CACHE UNTUK DITAMPILKAN DI HP CUSTOMER
                Cache::put('qr_status_' . $uniqueToken, 'success', now()->addMinutes(1));
                Cache::put('qr_invoice_' . $uniqueToken, [
                    'invoice_number' => $order->invoice_number,
                    'total'          => $order->total_tagihan,
                    'tagihan_uang'   => $order->bayar_uang,
                    'tagihan_poin'   => $order->bayar_poin,
                    'waktu'          => $order->created_at->format('d M Y, H:i'),
                    'items'          => $order->details,
                    'kasir_name'     => Auth::user()->nama ?? 'Kasir',
                    'catatan'        => $pesanNotifikasi,
                    'kartu_dipakai'  => $membership ? $membership->name : 'Reguler' // Opsional untuk info
                ], now()->addMinutes(5));

                DB::commit();

                // 8. UPDATE LAYAR KASIR
                $invoiceData = [
                    'invoice_number' => $order->invoice_number,
                    'customer_name'  => $customer->nama,
                    'total'          => $order->total_tagihan,
                    'tagihan_uang'   => $order->bayar_uang,
                    'tagihan_poin'   => $order->bayar_poin,
                    'waktu'          => $order->created_at->format('d M Y, H:i'),
                    'kasir_name'     => Auth::user()->nama ?? 'Kasir',
                    'catatan'        => $pesanNotifikasi,
                    'items'          => $order->details
                ];

                return back()->with('success_invoice', $invoiceData);
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Gagal mencatat transaksi ke database: ' . $e->getMessage());
            }
        } catch (DecryptException $e) {
            return back()->with('error', 'Format QR Code tidak dikenali. Pastikan Anda HANYA men-scan QR dari HP Customer aplikasi ini.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Sistem mendeteksi masalah: ' . $e->getMessage() . ' di baris ' . $e->getLine());
        }
    }
}
