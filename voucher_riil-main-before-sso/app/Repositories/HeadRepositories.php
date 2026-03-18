<?php

namespace App\Repositories;

use App\Models\Outlet;
use App\Models\Penerima;
use App\Models\Voucher;
use App\Models\VoucherBenefit;
use App\Repositories\Interfaces\HeadRepositoryInterfaces;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HeadRepositories implements HeadRepositoryInterfaces
{
    public function voucherHeadIndex()
    {
        $userOutletId = Auth::user()->outlet_id;

        // Ambil hanya voucher yang outlet_id-nya sama dengan user login
        $vouchers = Voucher::with(['outlet', 'penerima'])
            ->where('outlet_id', $userOutletId)
            ->latest()
            ->paginate(10);

        // Kalau mau tetap kirim daftar outlet untuk kebutuhan dropdown atau lainnya
        $outlets = Outlet::where('id', $userOutletId)->get();

        return view('head.voucher.index', compact('outlets', 'vouchers'));
    }

    public function voucherHeadStore(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'tgl_terbit_voucher' => 'required|date',
            'tgl_exp_voucher' => 'required|date|after_or_equal:tgl_terbit_voucher',
            'jumlah' => 'required|integer|min:1',
            'description' => 'required|string',
        ]);

        $outlet = Outlet::findOrFail($request->outlet_id);
        $kodeOutlet = $outlet->kode;
        $jumlah = $request->jumlah;

        // Cari nomor terakhir
        $lastVoucher = Voucher::where('outlet_id', $outlet->id)
            ->where('kode_voucher', 'LIKE', $kodeOutlet . '-%')
            ->orderByDesc('kode_voucher')
            ->first();

        $lastNumber = 0;
        if ($lastVoucher) {
            $lastNumber = (int) substr($lastVoucher->kode_voucher, strrpos($lastVoucher->kode_voucher, '-') + 1);
        }

        for ($i = 1; $i <= $jumlah; $i++) {
            $nextNumber = str_pad($lastNumber + $i, 3, '0', STR_PAD_LEFT);
            Voucher::create([
                'kode_voucher' => $kodeOutlet . '-' . $nextNumber,
                'outlet_id' => $outlet->id,
                'description' => $request->description, // ambil dari input
                'tgl_terbit_voucher' => $request->tgl_terbit_voucher,
                'tgl_exp_voucher' => $request->tgl_exp_voucher,
                'status' => 1
            ]);
        }

        return redirect()->route('voucherHead')->with('success', 'Voucher berhasil dibuat sebanyak ' . $jumlah . ' data.');
    }

    public function searchVoucherHead(Request $request)
    {
        $userOutletId = Auth::user()->outlet_id;
        $query = $request->input('query');
        $status = $request->input('status');

        $vouchers = Voucher::with(['outlet', 'penerima'])
            ->where('outlet_id', $userOutletId)
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('kode_voucher', 'like', '%' . $query . '%')
                        ->orWhereHas('penerima', function ($q2) use ($query) {
                            $q2->where('name', 'like', '%' . $query . '%');
                        });
                });
            })
            ->when($status && is_array($status), function ($q) use ($status) {
                $q->whereIn('status', $status);
            })
            ->latest()
            ->paginate(10);

        return view('head.voucher.partials.table', compact('vouchers'))->render();
    }

    public function getBenefits($id)
    {
        $voucher = Voucher::with([
            'voucherBenefits.voucher.penerima'
        ])->findOrFail($id);

        return response()->json($voucher->voucherBenefits);
    }

    public function transaksiHeadIndex()
    {
        $userOutletId = Auth::user()->outlet_id;

        $penerimas = Penerima::with(['outlet', 'voucher'])->where('outlet_id', $userOutletId)->orderBy('created_at', 'desc')->paginate(10); // atau ->paginate(10) jika pakai pagination

        return view('head.transaksi.index', compact('penerimas'));
    }

    public function searchTransaksiHead(Request $request)
    {
        $penerimas = Penerima::with(['outlet', 'voucher'])
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%')
                    ->orWhereHas('voucher', function ($q) use ($request) {
                        $q->where('kode_voucher', 'like', '%' . $request->search . '%');
                    });
            })
            ->latest()
            ->paginate(10);

        return view('head.transaksi.partials.table', compact('penerimas'));
    }

    public function scanHeadIndex()
    {
        return view('scanHead');
    }

    public function scanHeadBarcode(Request $request)
    {
        $kodeBenefit = $request->input('id'); // isi QR Code = kode_benefit
        $isPreview = $request->boolean('preview', false);
        $user = Auth::user();

        // Cari voucher_benefit berdasarkan kode_benefit + outlet yang sesuai user
        $voucherBenefit = VoucherBenefit::with(['voucher.outlet', 'voucher.penerima', 'benefit'])
            ->where('kode_benefit', $kodeBenefit)
            ->whereHas('benefit', function ($query) use ($user) {
                $query->where('outlet_id', $user->outlet_id);
            })
            ->first();

        if (!$voucherBenefit) {
            return response()->json(['error' => 'Benefit voucher bukan milik outlet Anda.']);
        }

        $voucher = $voucherBenefit->voucher;

        // Cek expired
        if ($voucher->tgl_exp_voucher && now()->gt(Carbon::parse($voucher->tgl_exp_voucher))) {
            return response()->json([
                'error' => 'Voucher ini sudah kadaluwarsa karena melebihi tanggal periode voucher'
            ]);
        }

        $nama = $voucher->penerima->name ?? 'Tidak diketahui';

        // Cek sudah digunakan
        if ($voucherBenefit->status == 0 && $voucherBenefit->discan) {
            $tgl = Carbon::parse($voucherBenefit->discan)->format('d/m/Y H:i');
            return response()->json([
                'error' => "Benefit telah digunakan oleh {$nama} pada {$tgl}"
            ]);
        }

        // Kalau preview, cukup tampilkan konfirmasi tanpa detail message
        if ($isPreview) {
            return response()->json(['preview' => true]);
        }

        // Update hanya voucher_benefits
        $voucherBenefit->status = 0;
        $voucherBenefit->discan = now();
        $voucherBenefit->tgl_pemakaian = now();
        $voucherBenefit->save();

        $tgl = $voucherBenefit->discan->format('d/m/Y H:i');

        return response()->json([
            'success' => "Benefit berhasil digunakan oleh {$nama} pada {$tgl}"
        ]);
    }
}
