<?php

namespace App\Models;

use App\Models\Outlet;
use App\Models\Penerima;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_voucher',
        'outlet_id',
        'user_id',
        'description',
        'tgl_terbit_voucher',
        'tgl_exp_voucher',
        'status',
        'discan'
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function penerima()
    {
        return $this->hasOne(Penerima::class, 'voucher_id');
    }

    public function voucherBenefits()
    {
        return $this->hasMany(VoucherBenefit::class);
    }
}
