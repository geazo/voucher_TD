<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestAdmin extends Model
{
    use HasFactory;

    protected $table = 'guest_admins';

    protected $fillable = [
        'kategori',
        'nik',
        'nama_depan',
        'nama_belakang',
        'perusahaan',
        'sim',
        'no_hp',
        'jam_masuk',
        'jam_keluar',
        'tujuan',
        'visit_location',
        'access_card',
        'foto_wajah',
        'foto_ktp',
    ];
}
