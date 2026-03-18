<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Models\Customer;
use App\Models\Membership;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AdminCustomerController extends Controller
{
    // Tampil List Customer (Superadmin)
    public function index(Request $request)
    {
        $search = $request->search;

        // Load relasi membership dan wallets (beserta transactions untuk kalkulasi saldo)
        $customers = Customer::with(['membership', 'wallets.transactions'])
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('notelp', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.customers', compact('customers')); // Pastikan nama view ini sesuai
    }
    // Fungsi Export Excel
    public function export()
    {
        $filename = 'Master_Customer_TamanDayu_' . date('Y-m-d_H-i') . '.xlsx';
        return Excel::download(new CustomersExport(), $filename);
    }
    // Form Registrasi (Kasir, Admin, Superadmin)
    public function create()
    {
        return view('admin.customer_register');
    }
    // Proses Registrasi
    public function store(Request $request)
    {
        // lowercase untuk konsistensi email, karena login menggunakan email
        if ($request->has('email')) {
            $request->merge([
                'email' => strtolower($request->email)
            ]);
        }
        // Validasi inputan
        $request->validate([
            'nama'   => 'required|string|max:255',
            'notelp' => 'required|string|unique:customers,notelp|max:15',
            'email'  => 'required|email|unique:customers,email',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $customer = Customer::create([
                    'nama'     => $request->nama,
                    'notelp'   => $request->notelp,
                    'email'    => $request->email,
                    'password' => 'password', // Password default, bisa diubah nanti
                    'f_aktif'  => true,
                ]);

                $operatorId = auth()->id();
                Wallet::create(['customer_id' => $customer->id, 'type' => 'Uang', 'operator_id' => $operatorId]);
                Wallet::create(['customer_id' => $customer->id, 'type' => 'Poin', 'operator_id' => $operatorId]);
            });

            return redirect()->route('topup.index')->with('success', 'Customer baru berhasil didaftarkan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mendaftarkan customer: ' . $e->getMessage());
        }
    }
    // Form Edit (Superadmin)
    public function edit(Customer $customer)
    {
        $memberships = Membership::all();
        return view('admin.customers_edit', compact('customer', 'memberships'));
    }

    // Proses Update (Superadmin)
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'notelp' => 'required|string|max:15|unique:customers,notelp,' . $customer->id,
            'membership_id' => 'nullable|exists:memberships,id',
            'f_aktif' => 'required|boolean',
        ]);

        $customer->update($request->all());
        return redirect()->route('customers.index')->with('success', 'Data customer berhasil diperbarui.');
    }
    // Proses Delete (Superadmin)
    // Proses Ubah Status Aktif/Non-Aktif (Superadmin)
    public function destroy(Customer $customer)
    {
        // Toggle status: Jika true jadi false, jika false jadi true
        $statusBaru = !$customer->f_aktif;

        // Simpan perubahan ke database
        $customer->update(['f_aktif' => $statusBaru]);

        // Buat pesan notifikasi yang dinamis
        $pesan = $statusBaru
            ? "Customer {$customer->nama} berhasil diaktifkan kembali."
            : "Customer {$customer->nama} berhasil dinon-aktifkan.";

        return redirect()->route('customers.index')->with('success', $pesan);
    }
}
