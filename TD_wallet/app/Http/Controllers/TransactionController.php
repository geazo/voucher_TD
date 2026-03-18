<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Transaction;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    // 1. Fungsi Menampilkan Halaman Transaksi
    public function index(Request $request)
    {
        // Ambil parameter '?type=' dari URL, default ke 'topup'
        $type = $request->query('type', 'topup');

        $query = Transaction::with(['wallet.customer', 'operator']);

        // Pisahkan logika: Topup (kredit) vs Pembayaran (debit)
        if ($type === 'topup') {
            $query->where('type', 'kredit');
        } else {
            $query->where('type', 'debit');
        }

        $transactions = $query->latest()->paginate(20);

        // Jaga agar saat pindah halaman (pagination), tab-nya tidak berubah
        $transactions->appends(['type' => $type]);

        return view('admin.transactions', compact('transactions', 'type'));
    }

    // 2. Fungsi Export ke Excel (CSV format)
    public function export(Request $request)
    {
        $type = $request->query('type', 'topup');

        $filename = $type === 'topup' ? 'Laporan_Topup_' : 'Laporan_Pembayaran_';
        $filename .= date('Ymd_His') . '.xlsx'; // Langsung format .xlsx

        return Excel::download(new TransactionsExport($type), $filename);
    }
}
