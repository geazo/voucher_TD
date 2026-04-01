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
            'qr_payload'      => 'required',
            'nominal_total'   => 'required|numeric|min:1000',
            'cart_data'       => 'nullable',
            'fallback_action' => 'nullable|in:tunai,topup'
        ]);

        try {
            $uniqueToken = Crypt::decryptString($request->qr_payload);
            $customerId = Cache::get('qr_customer_' . $uniqueToken);
            $tokenStatus = Cache::get('qr_status_' . $uniqueToken);
            $selectedMembershipId = Cache::get('qr_wallet_' . $uniqueToken);

            if (!$customerId || $tokenStatus !== 'pending') {
                return back()->with('error', 'QR Code tidak valid atau sudah kedaluwarsa.');
            }

            $customer = Customer::with(['wallets.membership'])->findOrFail($customerId);

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

            $membership = $dompetUang->membership ?? null;
            $persenDiskon = $membership ? $membership->diskon_belanja : 0;

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

            $totalTagihan = $request->nominal_total;
            $maksimalDiskonPoin = $totalTagihan * ($persenDiskon / 100);

            $tagihanPoin = 0;
            $tagihanUang = $totalTagihan;
            $pesanNotifikasi = '';

            if ($maksimalDiskonPoin > 0) {
                if ($saldoPoin <= 0) {
                    $pesanNotifikasi = "Saldo poin kosong/expired, tagihan dibebankan ke saldo uang.";
                } elseif ($saldoPoin < $maksimalDiskonPoin) {
                    $tagihanPoin = $saldoPoin;
                    $tagihanUang = $totalTagihan - $tagihanPoin;
                    $pesanNotifikasi = "Poin dihabiskan. Sisa tagihan dibebankan ke saldo uang.";
                } else {
                    $tagihanPoin = $maksimalDiskonPoin;
                    $tagihanUang = $totalTagihan - $tagihanPoin;
                }
            }


            $dibayarDompetUang = $tagihanUang;
            $bayarTunai = 0;

            if ($saldoUang < $tagihanUang) {
                $kekurangan = $tagihanUang - $saldoUang;

                // 1. Jika belum ada opsi yang dipilih, lemparkan kembali ke Kasir dengan data kekurangan
                if (!$request->filled('fallback_action')) {
                    return back()->with('insufficient_balance', [
                        'customer_name' => $customer->nama,
                        'saldo_uang'    => $saldoUang,
                        'tagihan_uang'  => $tagihanUang,
                        'kekurangan'    => $kekurangan
                    ])->withInput(); // withInput() mengembalikan payload QR dan nominal yang sudah discan
                }

                // 2. Jika Kasir memilih pelanggan ingin Topup
                if ($request->fallback_action === 'topup') {
                    // Batalkan QR, arahkan kasir ke halaman topup untuk customer ini
                    return redirect()->route('topup.index', ['customer_id' => $customer->id])
                        ->with('warning', 'Silakan topup sebesar Rp ' . number_format($kekurangan, 0, ',', '.') . ' untuk melanjutkan transaksi pelanggan ' . $customer->nama);
                }

                // 3. Jika Kasir memilih pelanggan bayar sisa dengan Tunai (Lanjutkan proses di bawah)
                if ($request->fallback_action === 'tunai') {
                    $dibayarDompetUang = $saldoUang;
                    $bayarTunai = $kekurangan;
                    $pesanTambahan = "Sisa kekurangan Rp " . number_format($bayarTunai, 0, ',', '.') . " dibayar TUNAI.";
                    $pesanNotifikasi = $pesanNotifikasi ? $pesanNotifikasi . " | " . $pesanTambahan : $pesanTambahan;
                }
            }

            DB::beginTransaction();
            try {
                $operatorId = Auth::id();

                $order = Order::create([
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                    'customer_id'    => $customer->id,
                    'operator_id'    => $operatorId,
                    'total_tagihan'  => $totalTagihan,
                    'bayar_uang'     => $dibayarDompetUang,
                    'bayar_poin'     => $tagihanPoin,
                    'bayar_tunai'    => $bayarTunai,
                ]);

                $cartData = json_decode($request->cart_data, true) ?? [];
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

                $potongSaldoFefo = function ($walletId, $jumlahPotong) {
                    $kreditTersedia = Transaction::where('wallet_id', $walletId)
                        ->where('type', 'kredit')
                        ->where('sisa_saldo', '>', 0)
                        ->where('expired_at', '>=', now()->toDateString())
                        ->orderBy('expired_at', 'asc')
                        ->orderBy('created_at', 'asc')
                        ->lockForUpdate()
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

                    if ($sisaTagihan > 0) {
                        throw new \Exception("Gagal memotong saldo secara penuh.");
                    }
                };

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

                if ($dibayarDompetUang > 0) {
                    $potongSaldoFefo($dompetUang->id, $dibayarDompetUang);
                    Transaction::create([
                        'wallet_id'   => $dompetUang->id,
                        'order_id'    => $order->id,
                        'type'        => 'debit',
                        'nominal'     => $dibayarDompetUang,
                        'operator_id' => $operatorId,
                        'keterangan'  => 'Pembayaran - ' . $order->invoice_number
                    ]);
                }

                $order->load('details');

                Cache::put('qr_status_' . $uniqueToken, 'success', now()->addMinutes(1));
                Cache::put('qr_invoice_' . $uniqueToken, [
                    'invoice_number' => $order->invoice_number,
                    'total'          => $order->total_tagihan,
                    'tagihan_uang'   => $order->bayar_uang,
                    'tagihan_poin'   => $order->bayar_poin,
                    'tagihan_tunai'  => $order->bayar_tunai,
                    'waktu'          => $order->created_at->format('d M Y, H:i'),
                    'items'          => $order->details,
                    'kasir_name'     => Auth::user()->nama ?? 'Kasir',
                    'catatan'        => $pesanNotifikasi,
                    'kartu_dipakai'  => $membership ? $membership->name : 'Reguler'
                ], now()->addMinutes(5));

                DB::commit();

                $invoiceData = [
                    'invoice_number' => $order->invoice_number,
                    'customer_name'  => $customer->nama,
                    'total'          => $order->total_tagihan,
                    'tagihan_uang'   => $order->bayar_uang,
                    'tagihan_poin'   => $order->bayar_poin,
                    'tagihan_tunai'  => $order->bayar_tunai,
                    'waktu'          => $order->created_at->format('d M Y, H:i'),
                    'kasir_name'     => Auth::user()->nama ?? 'Kasir',
                    'catatan'        => $pesanNotifikasi,
                    'items'          => $order->details
                ];

                return back()->with('success_invoice', $invoiceData);
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Gagal mencatat transaksi: ' . $e->getMessage());
            }
        } catch (DecryptException $e) {
            return back()->with('error', 'Format QR Code tidak dikenali.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Sistem mendeteksi masalah: ' . $e->getMessage());
        }
    }
}
