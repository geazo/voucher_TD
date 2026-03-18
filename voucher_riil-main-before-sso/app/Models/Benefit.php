<?php

namespace App\Models;

use App\Models\Outlet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Benefit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'outlet_id',
        'status',
        'photo',
        'redemption',
        'kode',
        'tc',
        'logo1',
        'logo2'
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function voucherBenefits()
    {
        return $this->hasMany(VoucherBenefit::class);
    }
}
