<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::orderBy('nama', 'asc')->get();
        return view('admin.items', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'  => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
        ]);

        Item::create($request->only('nama', 'harga'));

        return back()->with('success', 'Item/Layanan baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama'  => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
        ]);

        Item::findOrFail($id)->update($request->only('nama', 'harga'));

        return back()->with('success', 'Data item berhasil diperbarui!');
    }

    public function destroy($id)
    {
        Item::findOrFail($id)->delete();
        return back()->with('success', 'Item berhasil dihapus dari sistem!');
    }
}
