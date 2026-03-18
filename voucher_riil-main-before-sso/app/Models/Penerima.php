<?php

namespace App\Models;

use App\Models\Outlet;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penerima extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'tgl_pemakaian',
        'outlet_id',
        'voucher_id',
        'bill'
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
