<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function index()
    {
        // Ambil semua data membership
        $memberships = Membership::orderBy('id', 'asc')->get();

        return view('admin.memberships', compact('memberships'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input termasuk PREFIX
        $request->validate([
            'name'           => 'required|string|max:50|unique:memberships,name',
            'prefix'         => 'required|string|max:3|unique:memberships,prefix', // Maks 3 Huruf (Contoh: PL, GLD)
            'harga'          => 'required|numeric|min:0',
            'bonus_topup'    => 'required|numeric|min:0|max:100',
            'diskon_belanja' => 'required|numeric|min:0|max:100',
            'color_start'    => 'required|string|max:10',
            'color_end'      => 'required|string|max:10',
            'text_color'     => 'required|in:text-white,text-dark',
        ], [
            'name.unique'   => 'Nama tier membership ini sudah ada!',
            'prefix.unique' => 'Kode Prefix ini sudah digunakan oleh tier lain!',
            'prefix.max'    => 'Prefix maksimal 3 karakter saja.',
        ]);

        // Karena sudah ada di $fillable model, kita bisa langsung pakai all()
        Membership::create($request->all());

        return redirect()->back()->with('success', 'Tier Membership baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $membership = Membership::findOrFail($id);

        // 1. Validasi Update (Kecualikan ID saat ini untuk rule Unique)
        $request->validate([
            'name'           => 'required|string|max:50|unique:memberships,name,' . $membership->id,
            'prefix'         => 'required|string|max:2|unique:memberships,prefix,' . $membership->id,
            'harga'          => 'required|numeric|min:0',
            'bonus_topup'    => 'required|numeric|min:0|max:100',
            'diskon_belanja' => 'required|numeric|min:0|max:100',
            'color_start'    => 'required|string|max:10',
            'color_end'      => 'required|string|max:10',
            'text_color'     => 'required|in:text-white,text-dark',
        ], [
            'name.unique'   => 'Nama tier membership ini sudah digunakan!',
            'prefix.unique' => 'Kode Prefix ini sudah digunakan oleh tier lain!',
        ]);

        $membership->update($request->all());

        return redirect()->back()->with('success', 'Tier Membership berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $membership = Membership::findOrFail($id);

        // JARING PENGAMAN: Jangan hapus jika sedang dipakai customer
        if ($membership->customers()->count() > 0) {
            return redirect()->back()->with('error', 'Gagal dihapus! Tier ini sedang digunakan oleh beberapa customer.');
        }

        $membership->delete();

        return redirect()->back()->with('success', 'Tier Membership berhasil dihapus.');
    }
}
