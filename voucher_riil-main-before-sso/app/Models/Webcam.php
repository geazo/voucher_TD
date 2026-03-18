<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Webcam extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'webcams';

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

    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];
}
