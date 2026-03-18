<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Webcam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class WebcamController extends Controller
{
    public function index()
    {
        return view('webcam');
    }


    /**
     * Write code on Method
     *
     * @return response()
     */
    public function store(Request $request)
    {
        // Periksa apakah request memiliki gambar
        if (!$request->has('foto_ktp') || empty($request->foto_ktp)) {
            return back()->withErrors(['error' => 'Gambar tidak ditemukan']);
        }

        $img = $request->foto_ktp;
        $folderPath = "fotoKtp/"; // Folder dalam storage

        // Pastikan string memiliki format yang benar
        $image_parts = explode(";base64,", $img);
        if (count($image_parts) < 2) {
            return back()->withErrors(['error' => 'Format gambar tidak valid']);
        }

        // Ekstrak tipe gambar
        $image_type_aux = explode("image/", $image_parts[0]);
        if (count($image_type_aux) < 2) {
            return back()->withErrors(['error' => 'Tipe gambar tidak ditemukan']);
        }

        $image_type = $image_type_aux[1]; // Ekstensi gambar (png, jpg, dll.)
        $image_base64 = base64_decode($image_parts[1]);

        // Generate nama file unik
        $rand_code = Str::random(6);
        $fileName = time() . $rand_code . '.' . $image_type;

        // Simpan gambar ke storage/app/fotoKtp
        Storage::disk('public')->put("fotoKtp/$fileName", $image_base64);

        // Simpan ke database dengan path yang bisa diakses
        $id = Guest::create([
            'kategori' => $request->kategori,
            'nik' => $request->nik,
            'nama_depan' => $request->nama_depan,
            'nama_belakang' => $request->nama_belakang,
            'perusahaan' => $request->perusahaan,
            'no_hp' => $request->no_hp,
            'kepentingan' => $request->kepentingan,
            'lokasi_tujuan' => $request->lokasi_tujuan,
            'jam_masuk' => now()->setTimezone('Asia/Jakarta'),
            'foto_ktp' => "storage/fotoKtp/$fileName" // Path agar bisa diakses
        ])->id;

        // return redirect('photo/' . $id);
        return redirect('dashboard')->with('success', 'Data berhasil ditambahkan!');
    }

    // public function store(Request $request)
    // {
    //     // Validate the incoming request
    //     $request->validate([
    //         // 'name'          => 'required|string',
    //         // 'email'         => 'required|email',
    //         // 'password'      => 'required|min:6',
    //         'foto_ktp'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Added validation for 'foto_ktp'
    //         'kategori'      => 'required|string',
    //         'nik'           => 'required|string',
    //         'nama_depan'    => 'required|string',
    //         'nama_belakang' => 'required|string',
    //         'perusahaan'    => 'required|string',
    //         'no_hp'         => 'required|string',
    //         'tujuan'        => 'required|string',
    //         // 'visit_location' => 'required|string',
    //         // 'access_card'   => 'required|string',
    //     ]);

    //     // Handle file upload for foto_ktp
    //     if ($request->hasFile('foto_ktp')) {
    //         $file = $request->file('foto_ktp');
    //         $filename = time() . '_ktp.' . $file->getClientOriginalExtension();
    //         $path = $file->storeAs('fotoKtp', $filename, 'public'); // Store in storage/app/public/fotoKtp
    //         $foto_ktp_path = "storage/$path"; // Save the path for public access
    //     } else {
    //         $foto_ktp_path = null;
    //     }

    //     // Create the new record in the database
    //     $webcam = Webcam::create([
    //         'name'            => $request->name,
    //         'foto_ktp'       => $foto_ktp_path,
    //         'kategori'        => $request->kategori,
    //         'nik'             => $request->nik,
    //         'nama_depan'      => $request->nama_depan,
    //         'nama_belakang'   => $request->nama_belakang,
    //         'perusahaan'      => $request->perusahaan,
    //         'no_hp'           => $request->no_hp,
    //         'tujuan'          => $request->tujuan,
    //         'visit_location'  => $request->visit_location,
    //         'access_card'     => $request->access_card,
    //     ]);

    //     // Redirect to a route with the new webcam's id
    //     return redirect('photo/' . $webcam->id);
    // }


    public function photo($id)
    {
        $data = Webcam::where('id', $id)->first();
        return view('photo', compact('data'));
    }
}
