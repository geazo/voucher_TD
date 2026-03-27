<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOperatorController extends Controller
{
    // 1. Tampil List Operator
    public function index(Request $request)
    {
        $search = $request->search;

        $operators = Operator::when($search, function ($query) use ($search) {
            $query->where('nama', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10);

        return view('admin.operators', compact('operators'));
    }

    // 2. Form Tambah Operator
    public function create()
    {
        return view('admin.operators_crud');
    }

    // 3. Simpan Operator Baru
    public function store(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'email'    => 'required|email|unique:operators,email',
            'role'     => 'required|in:kasir,admin,superadmin',
            'password' => 'required|min:6'
        ]);

        // Password otomatis di-hash oleh model cast 'password' => 'hashed'
        Operator::create($request->all());

        return redirect()->route('operators.index')->with('success', 'Operator baru berhasil ditambahkan.');
    }

    // 4. Form Edit Operator
    public function edit(Operator $operator)
    {
        // PROTEKSI: Admin tidak boleh mengedit Superadmin
        if (Auth::user()->role === 'admin' && $operator->role === 'superadmin') {
            return back()->with('error', 'Akses ditolak! Admin tidak memiliki izin untuk mengedit data Superadmin.');
        }

        return view('admin.operators_crud', compact('operator'));
    }

    // 5. Update Data Operator
    public function update(Request $request, Operator $operator)
    {
        // PROTEKSI 1: Admin tidak boleh mengubah data Superadmin
        if (Auth::user()->role === 'admin' && $operator->role === 'superadmin') {
            return back()->with('error', 'Akses ditolak! Admin tidak memiliki izin untuk mengubah data Superadmin.');
        }
        $request->validate([
            'nama'     => 'required|string|max:255',
            'email'    => 'required|email|unique:operators,email,' . $operator->id,
            'role'     => 'required|in:kasir,admin,superadmin',
            'password' => 'nullable|min:6'
        ]);
        // PROTEKSI 2: Admin tidak boleh "mengangkat" seseorang menjadi Superadmin
        if (Auth::user()->role === 'admin' && $request->role === 'superadmin') {
            return back()->with('error', 'Akses ditolak! Hanya Superadmin yang dapat memberikan role Superadmin kepada pengguna lain.');
        }

        $data = $request->only(['nama', 'email', 'role']);
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }
        $operator->update($data);
        return redirect()->route('operators.index')->with('success', 'Data operator berhasil diperbarui.');
    }

    // 6. Hapus Operator
    public function destroy(Operator $operator)
    {
        // Mencegah menghapus diri sendiri
        if ($operator->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }
        // PROTEKSI: Admin tidak boleh menghapus Superadmin
        if (Auth::user()->role === 'admin' && $operator->role === 'superadmin') {
            return back()->with('error', 'Akses ditolak! Admin tidak memiliki izin untuk menghapus akun Superadmin.');
        }
        try {
            $operator->delete();
            return redirect()->route('operators.index')->with('success', 'Operator berhasil dihapus secara permanen.');
        } catch (\Exception $e) {
            return back()->with('error', 'Operator ini tidak bisa dihapus karena namanya sudah tercatat di dalam Riwayat Transaksi keuangan.');
        }
    }
}
