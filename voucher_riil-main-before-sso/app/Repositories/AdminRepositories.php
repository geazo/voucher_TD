<?php

namespace App\Repositories;

use App\Mail\VoucherMail;
use App\Models\Benefit;
use App\Models\Guest;
use App\Models\Outlet;
use App\Models\Penerima;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherBenefit;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Repositories\Interfaces\AdminRepositoryInterfaces;

class AdminRepositories implements AdminRepositoryInterfaces
{
    public function index()
    {
        $tamus = Guest::select([
            'nik',
            'nama_depan',
            'nama_belakang',
            'perusahaan',
            'no_hp',
            'jam_masuk',
            'jam_keluar',
            'kepentingan',
            'lokasi_tujuan',
            'visit_location',
            'access_card',
            'id',
        ])->orderBy('created_at', 'desc')->paginate(10);

        // dd($tamus);

        return view('dashboard', compact('tamus'));
    }

    public function scanIndex()
    {
        return view('scan');
    }

    public function scanBarcode(Request $request)
    {
        $kodeBenefit = $request->input('kode') ?? $request->input('id'); // support 'kode' & 'id'
        $isPreview = $request->boolean('preview', false);

        // Cari voucher_benefit berdasarkan kode_benefit
        $voucherBenefit = VoucherBenefit::with('voucher.outlet', 'benefit', 'voucher.penerima')
            ->where('kode_benefit', $kodeBenefit)
            ->first();

        if (!$voucherBenefit) {
            return response()->json([
                'redirect' => route('scan') . '?msg=Benefit tidak ditemukan'
            ]);
        }

        $voucher = $voucherBenefit->voucher;
        $nama    = $voucher->penerima->name ?? 'Tidak diketahui';
        $now     = Carbon::now();

        // Cek expired berdasarkan voucher parent
        if ($voucher->tgl_exp_voucher && $now->gt(Carbon::parse($voucher->tgl_exp_voucher))) {
            return response()->json([
                'redirect' => route('scan') . '?msg=Benefit ini sudah kadaluwarsa'
            ]);
        }

        // Cek sudah digunakan
        if ($voucherBenefit->status == 0 && $voucherBenefit->discan) {
            $tgl = Carbon::parse($voucherBenefit->discan)->format('d/m/Y H:i');
            return response()->json([
                'redirect' => route('scan') . '?msg=Benefit sudah digunakan oleh ' . $nama . ' pada ' . $tgl
            ]);
        }

        // Siapkan pesan khusus benefit
        $message =
            "Detail Benefit Anda:\n"
            . "Kode Benefit: {$voucherBenefit->kode_benefit}\n"
            . "Benefit: {$voucherBenefit->benefit->name}\n"
            . "Outlet: {$voucher->outlet->name}\n"
            . "Deskripsi: {$voucher->description}\n"
            . "Berlaku: " . Carbon::parse($voucher->tgl_terbit_voucher)->format('d/m/Y') . "\n"
            . "Kadaluwarsa: " . Carbon::parse($voucher->tgl_exp_voucher)->format('d/m/Y');

        // Kalau cuma preview, kirim data tanpa simpan
        if ($isPreview) {
            return response()->json(['message' => $message]);
        }

        // Update voucher_benefits saja
        $voucherBenefit->status = 0;
        $voucherBenefit->discan = now();
        $voucherBenefit->tgl_pemakaian = now();
        $voucherBenefit->save();

        $tgl = $voucherBenefit->discan->format('d/m/Y H:i');

        return response()->json([
            'redirect' => route('scan') . '?msg=Benefit berhasil digunakan oleh ' . $nama . ' pada ' . $tgl
        ]);
    }

    public function outletIndex()
    {
        $outlets = Outlet::select([
            'id',
            'name',
            'kode',
        ])->orderBy('created_at', 'asc')->paginate(10);

        // dd($outlets);

        return view('outlet.index', compact('outlets'));
    }

    public function outletStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'kode' => 'required|string|max:100|unique:outlets,kode',
        ]);

        Outlet::create([
            'name' => $request->name,
            'kode' => $request->kode,
        ]);

        return redirect()->route('outlet')->with('success', 'Outlet berhasil ditambahkan');
    }

    public function outletUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'kode' => 'required|string|max:100|unique:outlets,kode,' . $id,
        ]);

        $outlet = Outlet::findOrFail($id);

        $outlet->update([
            'name' => $request->name,
            'kode' => $request->kode,
        ]);

        return redirect()->route('outlet')->with('success', 'Outlet berhasil diperbarui');
    }

    public function outletDestroy($id)
    {
        $outlet = Outlet::findOrFail($id);
        $outlet->delete();

        return redirect()->route('outlet')->with('success', 'Outlet berhasil dihapus');
    }

    // Voucher
    public function voucherIndex(Request $request)
    {
        $outlets = Outlet::all();
        $vouchers = Voucher::with(['outlet', 'penerima'])->latest()->paginate(10);

        return view('voucher.index', compact('outlets', 'vouchers'));
    }

    // public function voucherStore(Request $request)
    // {
    //     $request->validate([
    //         'outlet_id' => 'required|exists:outlets,id',
    //         'tgl_terbit_voucher' => 'nullable|date',
    //         'tgl_exp_voucher' => 'nullable|date|after_or_equal:tgl_terbit_voucher',
    //         'jumlah' => 'required|integer|min:1',
    //         'description' => 'nullable|string',
    //     ]);

    //     $outlet = Outlet::findOrFail($request->outlet_id);
    //     $kodeOutlet = $outlet->kode;
    //     $jumlah = $request->jumlah;

    //     // Cari nomor terakhir
    //     $lastVoucher = Voucher::where('outlet_id', $outlet->id)
    //         ->where('kode_voucher', 'LIKE', $kodeOutlet . '-%')
    //         ->orderByDesc('kode_voucher')
    //         ->first();

    //     $lastNumber = 0;
    //     if ($lastVoucher) {
    //         $lastNumber = (int) substr($lastVoucher->kode_voucher, strrpos($lastVoucher->kode_voucher, '-') + 1);
    //     }

    //     for ($i = 1; $i <= $jumlah; $i++) {
    //         $nextNumber = str_pad($lastNumber + $i, 3, '0', STR_PAD_LEFT);
    //         $voucher = Voucher::create([
    //             'kode_voucher' => $kodeOutlet . '-' . $nextNumber,
    //             'outlet_id' => $outlet->id,
    //             'description' => $request->description, // ambil dari input
    //             'tgl_terbit_voucher' => $request->tgl_terbit_voucher,
    //             'tgl_exp_voucher' => $request->tgl_exp_voucher,
    //             'status' => 1
    //         ]);

    //         // Ambil semua master benefit
    //         $benefits = Benefit::all();

    //         foreach ($benefits as $benefit) {
    //             VoucherBenefit::create([
    //                 'voucher_id' => $voucher->id,
    //                 'benefit_id' => $benefit->id,
    //                 'kode_benefit' => $benefit->kode . '-B' . $voucher->kode_voucher . '-' . $benefit->id,
    //                 'status' => 1
    //             ]);
    //         }
    //     }

    //     return redirect()->route('voucher')->with('success', 'Voucher berhasil dibuat sebanyak ' . $jumlah . ' data.');
    // }

    public function voucherStore(Request $request)
    {
        $request->validate([
            'outlet_id'          => 'required|exists:outlets,id',
            'tgl_terbit_voucher' => 'nullable|date',
            'tgl_exp_voucher'    => 'nullable|date|after_or_equal:tgl_terbit_voucher',
            'jumlah'             => 'required|integer|min:1',
            'description'        => 'nullable|string',
        ]);

        $outlet     = Outlet::findOrFail($request->outlet_id);
        $kodeOutlet = $outlet->kode;
        $jumlah     = $request->jumlah;

        // cari nomor voucher terakhir di outlet ini
        $lastVoucher = Voucher::where('outlet_id', $outlet->id)
            ->where('kode_voucher', 'LIKE', $kodeOutlet . '-%')
            ->orderByDesc('kode_voucher')
            ->first();

        $lastNumber = 0;
        if ($lastVoucher) {
            $lastNumber = (int) substr($lastVoucher->kode_voucher, strrpos($lastVoucher->kode_voucher, '-') + 1);
        }

        // ambil id outlet Padel Malang (jika ada)
        $padelOutlet = Outlet::where('kode', 'PDM')->first();
        $chamasBali = Outlet::where('kode', 'CHB')->first();

        for ($i = 1; $i <= $jumlah; $i++) {
            $nextNumber = str_pad($lastNumber + $i, 3, '0', STR_PAD_LEFT);

            $voucher = Voucher::create([
                'kode_voucher'      => $kodeOutlet . '-' . $nextNumber,
                'outlet_id'         => $outlet->id,
                'description'       => $request->description,
                'tgl_terbit_voucher' => $request->tgl_terbit_voucher,
                'tgl_exp_voucher'   => $request->tgl_exp_voucher,
                'status'            => 1,
            ]);

            // tentukan benefit sesuai outlet
            if ($padelOutlet && $outlet->id === $padelOutlet->id) {
                // kalau outlet adalah Padel Malang → hanya benefit Padel Malang
                $benefits = Benefit::where('outlet_id', $outlet->id)->get();
            } else if ($chamasBali && $outlet->id === $chamasBali->id) {
                // kalau outlet adalah Chamas Bali → hanya benefit Chamas Bali
                $benefits = Benefit::where('outlet_id', $outlet->id)->get();
            } else {
                // outlet lain → semua benefit kecuali punya Padel Malang
                $benefitsQuery = Benefit::query();
                if ($padelOutlet) {
                    $benefitsQuery->where('outlet_id', '!=', $padelOutlet->id);
                }
                if ($chamasBali) {
                    $benefitsQuery->where('outlet_id', '!=', $chamasBali->id);
                }
                $benefits = $benefitsQuery->get();
            }

            // attach ke voucher_benefits
            foreach ($benefits as $benefit) {
                VoucherBenefit::create([
                    'voucher_id'   => $voucher->id,
                    'benefit_id'   => $benefit->id,
                    'kode_benefit' => $benefit->kode . '-B' . $voucher->kode_voucher . '-' . $benefit->id,
                    'status'       => 1,
                ]);
            }
        }

        return redirect()->route('voucher')->with('success', 'Voucher berhasil dibuat sebanyak ' . $jumlah . ' data.');
    }

    public function searchVoucher(Request $request)
    {
        $vouchers = Voucher::with(['outlet', 'penerima'])
            ->when($request->search || $request->status, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    // Filter berdasarkan search
                    if ($request->search) {
                        $q->where('kode_voucher', 'like', '%' . $request->search . '%')
                            ->orWhereHas('penerima', function ($p) use ($request) {
                                $p->where('name', 'like', '%' . $request->search . '%');
                            });
                    }

                    // Filter berdasarkan status
                    if ($request->has('status') && is_array($request->status)) {
                        $q->whereIn('status', $request->status);
                    }
                });
            })
            ->latest()
            ->paginate(10);

        return view('voucher.partials.table', compact('vouchers'))->render();
    }

    // getBenefits
    public function getBenefits($id)
    {
        $voucher = Voucher::with([
            'voucherBenefits.voucher.penerima'
        ])->findOrFail($id);

        return response()->json($voucher->voucherBenefits);
    }


    public function transaksiIndex()
    {
        $penerimas = Penerima::with(['outlet', 'voucher'])->orderBy('created_at', 'desc')->paginate(10); // atau ->paginate(10) jika pakai pagination

        return view('transaksi.index', compact('penerimas'));
    }

    public function sendVoucher($penerimaId)
    {
        $penerima = Penerima::with('outlet', 'voucher.voucherBenefits.benefit')->findOrFail($penerimaId);

        if (!is_null($penerima->voucher_id)) {
            return back()->with('error', 'Penerima ini sudah memiliki voucher.');
        }

        // 🔹 Cek email valid sebelum update voucher
        if (empty($penerima->email) || !filter_var($penerima->email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'Email tidak valid atau tidak ada.');
        }

        $voucher = Voucher::where('outlet_id', $penerima->outlet_id)
            ->whereNull('user_id')
            ->where('status', 1)
            ->first();

        if (!$voucher) {
            return back()->with('error', 'Voucher untuk outlet ini sudah habis.');
        }

        $now = Carbon::now();
        if ($voucher->tgl_exp_voucher && $now->gt(Carbon::parse($voucher->tgl_exp_voucher))) {
            return back()->with('error', 'Voucher tidak dikirim karena sudah kadaluwarsa, melebihi tanggal periode.');
        }

        // ✅ Update voucher hanya jika email valid
        $voucher->update([
            'user_id' => $penerima->id,
            'status' => 2,
            'tgl_terbit_voucher' => $now,
            'tgl_exp_voucher' => $now->copy()->addMonths(3),
        ]);

        DB::table('voucher_benefits')
            ->where('voucher_id', $voucher->id)
            ->update([
                'status' => 2,
                'tgl_terbit_voucher' => $now,
                'tgl_exp_voucher' => $now->copy()->addMonths(3),
            ]);

        $penerima->update(['voucher_id' => $voucher->id]);

        // Ambil ulang voucher beserta relasinya
        $voucher = Voucher::with('voucherBenefits.benefit')->find($voucher->id);

        // Generate QR code
        foreach ($voucher->voucherBenefits as $vb) {
            $qrImage = QrCode::format('png')->size(200)->generate($vb->kode_benefit);
            $qrFileName = 'vb-' . $vb->id . '-' . Str::uuid() . '.png';
            Storage::disk('public')->put("qrcodes/{$qrFileName}", $qrImage);
            $vb->qr_path = public_path("storage/qrcodes/{$qrFileName}");
        }

        // Generate PDF
        $pdf = Pdf::loadView('pdf.voucher', [
            'penerima' => $penerima,
            'voucher'  => $voucher
        ])->setPaper('a4', 'landscape');

        $pdfFileName = 'voucher-' . Str::uuid() . '.pdf';
        Storage::disk('public')->put("pdfs/{$pdfFileName}", $pdf->output());
        $pdfUrl = asset("storage/pdfs/{$pdfFileName}");

        // Kirim email
        Mail::to($penerima->email)->send(new VoucherMail($penerima, $voucher, $pdfUrl));

        return back()->with('success', 'Voucher berhasil dikirim ke ' . $penerima->email);
    }

    public function sendVoucherWhatsApp($penerimaId)
    {
        $penerima = Penerima::with('outlet', 'voucher.voucherBenefits.benefit')->findOrFail($penerimaId);
        $now = Carbon::now();

        // 🔹 Cek nomor WA valid sebelum update voucher
        $phone = preg_replace('/[^0-9]/', '', $penerima->phone);

        // Nomor kosong atau tidak memenuhi panjang minimal 9 digit
        if (empty($phone) || strlen($phone) < 9) {
            return back()->with('error', 'Nomor WhatsApp tidak valid atau tidak ada.');
        }

        // Convert 0... ke format internasional
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // 🔹 Ambil voucher kalau belum ada
        if (!$penerima->voucher) {
            $voucher = Voucher::where('outlet_id', $penerima->outlet_id)
                ->whereNull('user_id')
                ->where('status', 1)
                ->first();

            if (!$voucher) return back()->with('error', 'Voucher habis.');
            if ($voucher->tgl_exp_voucher && $now->gt(Carbon::parse($voucher->tgl_exp_voucher)))
                return back()->with('error', 'Voucher sudah kadaluwarsa.');

            // ✅ Update voucher hanya kalau WA valid
            $voucher->update([
                'user_id' => $penerima->id,
                'status' => 2,
                'tgl_terbit_voucher' => $now,
                'tgl_exp_voucher' => $now->copy()->addMonths(3),
            ]);

            DB::table('voucher_benefits')
                ->where('voucher_id', $voucher->id)
                ->update([
                    'status' => 2,
                    'tgl_terbit_voucher' => $now,
                    'tgl_exp_voucher' => $now->copy()->addMonths(3),
                ]);

            $penerima->update(['voucher_id' => $voucher->id]);
        } else {
            $voucher = $penerima->voucher;
        }

        // 🔹 Generate QR code
        foreach ($voucher->voucherBenefits as $vb) {
            $qrImage = QrCode::format('png')->size(200)->generate($vb->kode_benefit);
            $qrFileName = 'vb-' . $vb->id . '-' . Str::uuid() . '.png';
            Storage::disk('public')->put("qrcodes/{$qrFileName}", $qrImage);
            $vb->qr_path = public_path("storage/qrcodes/{$qrFileName}");
        }

        // 🔹 Generate PDF
        $pdf = Pdf::loadView('pdf.voucher', [
            'penerima' => $penerima,
            'voucher' => $voucher
        ])->setPaper('a4', 'landscape');

        $pdfFileName = 'voucher-' . Str::uuid() . '.pdf';
        Storage::disk('public')->put("pdfs/{$pdfFileName}", $pdf->output());
        $pdfUrl = asset("storage/pdfs/{$pdfFileName}");

        // 🔹 Deteksi Padel Malang
        $outlet = $voucher->outlet ?: $penerima->outlet;
        $isPadelMalang = false;
        if ($outlet) {
            $isPadelMalang = ($outlet->kode === 'PADM') || (strtolower($outlet->name) === 'padel malang');
        }

        $lines = [
            "*Dear Mr. {$penerima->name},*",
            "",
            "We are pleased to present your exclusive voucher:",
            "*Voucher ID:* {$voucher->kode_voucher}",
            "*Outlet:* " . ($outlet ? $outlet->name : '-'),
            // "*Deskripsi:* {$voucher->description}",
            "*Valid From:* " . Carbon::parse($voucher->tgl_terbit_voucher)->format('d/m/Y'),
            "*Expiry Date:* " . Carbon::parse($voucher->tgl_exp_voucher)->format('d/m/Y'),
            "",
            "For your convenience, please download your QR code from the PDF via the link below:",
            $pdfUrl,
            "",
            "We look forward to providing you with an exceptional dining experience.",
            "With warmest regards,"
        ];

        // Tambahkan khusus Padel Malang
        if ($isPadelMalang) {
            // Sisipkan keterangan setelah baris “We are pleased ...”
            array_splice($lines, 2, 0, ["*Note:* This voucher is issued in relation to the Padel Malang event."]);
        }

        $message = implode("\n", $lines);

        // // 🔹 Pesan WA
        // $message = "*Dear Mr. {$penerima->name},*\n\n"
        //     . "We are pleased to present your exclusive voucher:\n"
        //     . "*Voucher ID:* {$voucher->kode_voucher}\n"
        //     . "*Outlet:* {$voucher->outlet->name}\n"
        //     // . "*Deskripsi:* {$voucher->description}\n"
        //     . "*Valid From:* " . Carbon::parse($voucher->tgl_terbit_voucher)->format('d/m/Y') . "\n"
        //     . "*Expiry Date:* " . Carbon::parse($voucher->tgl_exp_voucher)->format('d/m/Y') . "\n\n"
        //     . "For your convenience, please download your QR code from the PDF via the link below:\n{$pdfUrl}\n\n"
        //     . "We look forward to providing you with an exceptional dining experience.\n"
        //     . "With warmest regards,";

        // 🔹 Redirect ke WA
        $waUrl = "https://api.whatsapp.com/send?phone={$phone}&text=" . urlencode($message);
        return redirect()->away($waUrl);
    }

    public function searchTransaksi(Request $request)
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

        return view('transaksi.partials.table', compact('penerimas'))->render();
    }


    public function userIndex(Request $request)
    {
        $query = User::with('outlet')->where('role_as', 2);

        if ($request->filled('search')) {
            $searchTerms = explode(' ', $request->search);
            foreach ($searchTerms as $term) {
                $query->where('name', 'like', '%' . $term . '%');
            }
        }

        $customers = $query->orderBy('name')->paginate(10)->appends(['search' => $request->search]);
        $outlets = Outlet::all();

        if ($request->ajax()) {
            return view('user.partials.table', compact('customers'))->render();
        }

        return view('user.index', compact('customers', 'outlets'));
    }

    public function userStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'outlet_id' => 'required|exists:outlets,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_as' => 2,
            'outlet_id' => $request->outlet_id,
        ]);

        return redirect()->route('user')->with('success', 'User berhasil ditambahkan.');
    }

    public function userUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'outlet_id' => 'required|exists:outlets,id',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->outlet_id = $request->outlet_id;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('user')->with('success', 'User berhasil diperbarui.');
    }

    public function userDestroy($id)
    {
        User::destroy($id);
        return redirect()->route('user')->with('success', 'User berhasil dihapus.');
    }

    public function userShow($id)
    {
        return User::findOrFail($id);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xls,xlsx',
        ]);

        $file = $request->file('excel_file');
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Validasi header
        $header = $rows['0'];
        $expected = ['Nama', 'Email', 'No. WA', 'Nama Outlet'];
        foreach ($expected as $i => $col) {
            if (!isset($header[$i]) || trim($header[$i]) !== $col) {
                return back()->with('error', 'Format header kolom tidak sesuai. Harus: ' . implode(', ', $expected));
            }
        }

        unset($rows[0]);

        $countSuccess = 0;
        foreach ($rows as $index => $row) {
            $name = $row[0] ?? null;
            $email = $row[1] ?? null;
            $phone = $row[2] ?? null;
            $outletName = $row[3] ?? null;

            if (!$name || !$phone || !$outletName) continue;

            $outlet = Outlet::where('name', $outletName)->first();
            if (!$outlet) continue;

            Penerima::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'outlet_id' => $outlet->id,
            ]);

            $countSuccess++;
        }

        return back()->with('success', "$countSuccess data berhasil diimport.");
    }

    // Benefit
    public function benefitIndex(Request $request)
    {
        $outlets = Outlet::all();
        $benefits = Benefit::with('outlet')->latest()->paginate(10);

        return view('benefit.index', compact(['outlets', 'benefits']));
    }

    public function benefitStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'outlet_id' => 'required|exists:outlets,id',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'redemption' => 'nullable|string',
            'kode' => 'nullable|string',
        ]);

        $data = $request->only(['name', 'outlet_id', 'redemption', 'kode']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('benefits', 'public');
        }

        Benefit::create($data);

        return redirect()->route('benefit')->with('success', 'Benefit berhasil ditambahkan');
    }

    public function benefitUpdate(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'outlet_id' => 'required|exists:outlets,id',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'redemption' => 'nullable|string',
            'kode' => 'nullable|string',
        ]);

        $benefit = Benefit::findOrFail($id);
        $data = $request->only(['name', 'outlet_id', 'redemption', 'kode']);

        if ($request->hasFile('photo')) {
            // hapus foto lama kalau ada
            if ($benefit->photo && Storage::disk('public')->exists($benefit->photo)) {
                Storage::disk('public')->delete($benefit->photo);
            }
            $data['photo'] = $request->file('photo')->store('benefits', 'public');
        }

        $benefit->update($data);

        return redirect()->route('benefit')->with('success', 'Benefit berhasil diperbarui');
    }


    public function benefitDestroy($id)
    {
        $benefit = Benefit::findOrFail($id);
        $benefit->delete();

        return redirect()->route('benefit')->with('success', 'Benefit berhasil dihapus');
    }

    public function resetVoucher($id)
    {
        $penerima = Penerima::findOrFail($id);

        // Simpan voucher lama
        $voucher = $penerima->voucher;

        // Reset voucher_id penerima
        $penerima->update(['voucher_id' => null]);

        // Update status voucher jika ada
        if ($voucher) {
            $voucher->update([
                'status' => 1, // status kembali ke tersedia
                'tgl_terbit_voucher' => null,
                'tgl_exp_voucher' => null,
                'tgl_pemakaian' => null,
                'discan' => null,
                'user_id' => null,
            ]);
        }

        return back()->with('success', 'Voucher penerima berhasil direset.');
    }

    public function editPenerima($id)
    {
        $penerima = Penerima::with('outlet')->findOrFail($id);
        $outlets = Outlet::all(['id', 'name']);
        return response()->json([
            'penerima' => $penerima,
            'outlets' => $outlets
        ]);
    }

    public function updatePenerima(Request $request, $id)
    {
        $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'bill' => 'nullable|string'
        ]);

        $penerima = Penerima::findOrFail($id);
        $penerima->update($request->only('outlet_id', 'name', 'email', 'phone', 'bill'));

        return back()->with('success', 'Data penerima berhasil diperbarui.');
    }
}
