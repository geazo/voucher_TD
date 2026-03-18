<?php

namespace App\Repositories;

use App\Models\Outlet;
use App\Models\Penerima;
use App\Repositories\Interfaces\GuestRepositoryInterfaces;
use Illuminate\Http\Request;

class GuestRepositories implements GuestRepositoryInterfaces
{
    public function index()
    {
        $outlets = Outlet::whereNotIn('id', [7, 8])->get();

        return view('guest', compact('outlets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email',
            'phone' => 'required',
            'outlet_id' => 'required|exists:outlets,id',
            'bill' => 'required',
            'pin' => 'required',
        ]);

        // Normalisasi nomor telepon: ganti 0 di awal menjadi 62
        $phone = $request->phone;
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        Penerima::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone, // pakai nomor yang sudah diubah
            'outlet_id' => $request->outlet_id,
            'tgl_pemakaian' => null,
            'bill' => $request->bill
        ]);

        // return view('thankyou');
        return redirect()->route('thanks')->with('success', 'Terima kasih, data Anda telah tersimpan.');
    }

    public function thanks()
    {
        return view('thankyou');
    }
}
