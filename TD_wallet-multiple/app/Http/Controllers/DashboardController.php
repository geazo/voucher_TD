<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomer = Customer::count();
        $totalBelanjaHariIni = Transaction::whereDate('created_at', today())
            ->where('type', 'debit')
            ->whereHas('wallet', function ($query) {
                $query->where('type', 'Uang'); // Hanya hitung transaksi pada dompet Uang
            })
            ->count();
        $totalTopupHariIni = Transaction::whereDate('created_at', today())
            ->where('type', 'kredit')
            ->whereHas('wallet', function ($query) {
                $query->where('type', 'Uang'); // Hanya hitung transaksi pada dompet Uang
            })
            ->count();

        return view('dashboard', compact('totalCustomer', 'totalBelanjaHariIni', 'totalTopupHariIni'));
    }
}
