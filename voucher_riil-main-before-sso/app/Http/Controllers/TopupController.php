<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Outlet;
use Illuminate\Http\Request;

class TopupController extends Controller
{
    public function index()
    {
        // Ambil semua customer untuk dropdown awal
        $customers = \App\Models\Customer::all();
        $outlets = \App\Models\Outlet::all();

        return view('topup', compact('customers', 'outlets'));
    }

    // Fungsi untuk dipanggil via AJAX/Fetch saat nomor telp diketik
    public function getCustomerByPhone(Request $request)
    {
        $customer = \App\Models\Customer::where('notelp', $request->phone)->first();

        if ($customer) {
            return response()->json([
                'status' => 'success',
                'id' => $customer->id,
                'name' => $customer->name,
                'balance' => $customer->balance
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Not found']);
    }


    public function store(Request $request)
    {
        // Membersihkan titik dari input amount
        if ($request->has('amount')) {
            $cleanAmount = str_replace('.', '', $request->amount);
            $request->merge(['amount' => $cleanAmount]);
        }

        // Sekarang validasi numeric akan berhasil karena isinya sudah murni angka
        $request->validate([
            'phone'  => 'required',
            'amount' => 'required|numeric|min:1000',
        ]);

        // Cari customer berdasarkan nomor telepon
        $customer = \App\Models\Customer::where('notelp', $request->phone)->first();

        if (!$customer) {
            return back()->with('error', 'Customer tidak ditemukan!');
        }

        // Tambahkan saldo
        $customer->increment('balance', $request->amount);

        return redirect()->route('topup.index')->with('success', 'Topup Berhasil!');
    }
}
