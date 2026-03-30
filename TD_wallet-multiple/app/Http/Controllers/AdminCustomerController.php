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
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class AdminCustomerController extends Controller
{
    // Tampil List Customer (Superadmin)
    public function index(Request $request)
    {
        $search = $request->search;
        // Load relasi membership dan wallet
        $customers = Customer::with(['wallets.membership', 'wallets.transactions'])
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

    public function show(Request $request, $id)
    {
        $customer = Customer::with('wallets.membership')->findOrFail($id);
        $membershipId = $request->query('membership_id');
        if ($membershipId) {
            $wallets = $customer->wallets->where('membership_id', $membershipId);
        } else {
            $wallets = $customer->wallets->whereNull('membership_id');
        }
        if ($wallets->isEmpty()) {
            return redirect()->route('customers.index')->with('error', 'Rekening / Dompet tidak ditemukan.');
        }

        $dompetUang = $wallets->where('type', 'Uang')->first();
        $dompetPoin = $wallets->where('type', 'Poin')->first();
        $membership = $wallets->first()->membership;
        $walletIds = $wallets->pluck('id')->toArray();
        $transactions = Transaction::leftJoin('operators', 'transactions.operator_id', '=', 'operators.id')
            ->whereIn('transactions.wallet_id', $walletIds)
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

        return view('admin.customers_show', compact('customer', 'dompetUang', 'dompetPoin', 'transactions', 'membership'));
    }

    // Proses Update (Superadmin)
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'notelp' => 'required|string|max:15|unique:customers,notelp,' . $customer->id,
            'f_aktif' => 'required|boolean',
            'kota_domisili' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan,Lainnya',
        ]);

        $customer->update($request->all());
        return redirect()->route('customers.index')->with('success', 'Data customer berhasil diperbarui.');
    }

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

    public function export()
    {
        $filename = 'Master_Customer_TamanDayu_' . date('Y-m-d_H-i') . '.xlsx';
        $memberships = Membership::orderBy('id', 'asc')->get();
        return Excel::download(new CustomersExport($memberships), $filename);
    }
}
