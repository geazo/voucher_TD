<?php

namespace App\Models;

use App\Models\Voucher;
use App\Models\Penerima;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kode'
    ];

    public function voucher()
    {
        return $this->hasMany(Voucher::class);
    }

    public function penerimas()
    {
        return $this->hasMany(Penerima::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
