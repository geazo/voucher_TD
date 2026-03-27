<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Models\Customer;
use App\Models\Membership;
use App\Models\Transaction;
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
            'kota_domisili' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan,Lainnya',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $customer = Customer::create([
                    'nama'     => $request->nama,
                    'notelp'   => $request->notelp,
                    'email'    => $request->email,
                    'kota_domisili' => $request->kota_domisili,
                    'gender'        => $request->gender,
                    'password' => 'password', // Password default, bisa diubah nanti
                    'f_aktif'  => true,
                ]);
                $rekeningUtama = Wallet::generateNoRekening(null);
                $operatorId = auth()->id();
                // 1. Buat Dompet Uang menggunakan generate-an rekening utama
                Wallet::create([
                    'no_rekening' => $rekeningUtama,
                    'customer_id' => $customer->id,
                    'type' => 'Uang',
                    'operator_id' => $operatorId
                ]);
                // 2. Buat Dompet Poin menggunakan rekening utama yang SAMA
                Wallet::create([
                    'no_rekening' => $rekeningUtama,
                    'customer_id' => $customer->id,
                    'type' => 'Poin',
                    'operator_id' => $operatorId
                ]);
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
    public function show($id)
    {
        $customer = Customer::with('wallets')->findOrFail($id);
        $dompetUang = $customer->wallets->where('type', 'Uang')->first();
        $dompetPoin = $customer->wallets->where('type', 'Poin')->first();

        // GABUNGKAN TRANSAKSI YANG TERJADI DI DETIK YANG SAMA
        $transactions = Transaction::join('wallets', 'transactions.wallet_id', '=', 'wallets.id')
            ->leftJoin('operators', 'transactions.operator_id', '=', 'operators.id')
            ->where('wallets.customer_id', $id)
            ->selectRaw('
                transactions.created_at,
                transactions.type,
                SUM(transactions.nominal) as nominal,
                SUM(transactions.sisa_saldo) as sisa_saldo,
                MAX(transactions.expired_at) as expired_at,
                MIN(transactions.id) as id,
                MAX(transactions.keterangan) as keterangan,
                MAX(operators.nama) as operator_nama
            ')
            ->groupBy('transactions.created_at', 'transactions.type')
            ->orderBy('transactions.created_at', 'desc')
            ->paginate(15);

        return view('admin.customers_show', compact('customer', 'dompetUang', 'dompetPoin', 'transactions'));
    }

    // Proses Edit Master Customer
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'notelp' => 'required|string|max:15|unique:customers,notelp,' . $customer->id,
            'membership_id' => 'nullable|exists:memberships,id',
            'f_aktif' => 'required|boolean',
            'kota_domisili' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan,Lainnya',
        ]);

        $customer->update($request->all());
        return redirect()->route('customers.index')->with('success', 'Data customer berhasil diperbarui.');
    }

    // on off f_aktif customer Master Customer
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
